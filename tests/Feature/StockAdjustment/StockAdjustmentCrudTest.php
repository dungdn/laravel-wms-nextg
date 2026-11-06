<?php

namespace Tests\Feature\StockAdjustment;

use App\Livewire\StockAdjustment\Index;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockAdjustmentCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Warehouse $warehouse;

    private Product $product1;

    private Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $category = Category::create([
            'code' => 'CAT_ADJUST',
            'name' => 'Adjustment Category',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'code' => 'SUP_ADJUST',
            'name' => 'Adjustment Supplier',
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'code' => 'WH_AUDIT',
            'name' => 'Audit & Inventory Warehouse',
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'SKU-ADJ-01',
            'name' => 'Heavy Duty Stapler',
            'unit' => 'pcs',
            'cost_price' => 15.00,
            'selling_price' => 25.00,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'SKU-ADJ-02',
            'name' => 'Thermal Receipt Paper',
            'unit' => 'box',
            'cost_price' => 22.00,
            'selling_price' => 35.00,
            'is_active' => true,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('stock-adjustments.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_stock_adjustment_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('stock-adjustments.index'));

        $response->assertOk()
            ->assertSee('Stock Adjustments')
            ->assertSee('New Adjustment');
    }

    public function test_it_displays_stock_adjustment_records_and_searches_by_reference(): void
    {
        $adj1 = StockAdjustment::create([
            'reference_no' => 'ADJ-20261106-0001',
            'warehouse_id' => $this->warehouse->id,
            'adjustment_date' => now()->toDateString(),
            'reason' => 'Cycle Count',
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        $adj2 = StockAdjustment::create([
            'reference_no' => 'ADJ-20261106-0002',
            'warehouse_id' => $this->warehouse->id,
            'adjustment_date' => now()->toDateString(),
            'reason' => 'Damaged Goods',
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('ADJ-20261106-0001')
            ->assertSee('ADJ-20261106-0002')
            ->set('search', '0001')
            ->assertViewHas('stockAdjustments', function ($adjustments) use ($adj1) {
                return $adjustments->total() === 1
                    && $adjustments->first()->id === $adj1->id;
            });
    }

    public function test_it_can_create_a_stock_adjustment_reconciling_both_surplus_and_deficit_and_logs_audit_transactions(): void
    {
        // Setup initial inventory: product1 has 50, product2 has 30
        Inventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product1->id,
            'quantity' => 50,
        ]);

        Inventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product2->id,
            'quantity' => 30,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->assertDispatched('open-modal', 'stock-adjustment-create-modal')
            ->set('reference_no', 'ADJ-TEST-5555')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('adjustment_date', '2026-11-06')
            ->set('reason', 'Audit Variance')
            ->set('note', 'Annual Q4 physical stock count reconciliation')
            ->set('items', [
                [
                    'product_id' => $this->product1->id,
                    'adjusted_quantity' => 45, // Deficit: 50 -> 45 (-5)
                    'note' => 'Damaged during forklift transit',
                ],
                [
                    'product_id' => $this->product2->id,
                    'adjusted_quantity' => 38, // Surplus: 30 -> 38 (+8)
                    'note' => 'Unpacked buffer box found',
                ],
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'stock-adjustment-create-modal');

        // 1. Verify Stock Adjustment Header
        $this->assertDatabaseHas('stock_adjustments', [
            'reference_no' => 'ADJ-TEST-5555',
            'warehouse_id' => $this->warehouse->id,
            'reason' => 'Audit Variance',
            'status' => 'completed',
            'note' => 'Annual Q4 physical stock count reconciliation',
        ]);

        $adjustment = StockAdjustment::where('reference_no', 'ADJ-TEST-5555')->first();

        // 2. Verify Line Items
        $this->assertDatabaseHas('stock_adjustment_items', [
            'stock_adjustment_id' => $adjustment->id,
            'product_id' => $this->product1->id,
            'current_quantity' => 50,
            'adjusted_quantity' => 45,
            'quantity_change' => -5,
            'note' => 'Damaged during forklift transit',
        ]);

        $this->assertDatabaseHas('stock_adjustment_items', [
            'stock_adjustment_id' => $adjustment->id,
            'product_id' => $this->product2->id,
            'current_quantity' => 30,
            'adjusted_quantity' => 38,
            'quantity_change' => 8,
            'note' => 'Unpacked buffer box found',
        ]);

        $this->assertEquals(2, $adjustment->total_items);
        $this->assertEquals(3, $adjustment->total_quantity_change); // -5 + 8 = +3 net change

        // 3. Verify Inventories Reconciled
        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product1->id,
            'quantity' => 45,
        ]);

        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product2->id,
            'quantity' => 38,
        ]);

        // 4. Verify Inventory Audit Transactions Logged
        $this->assertDatabaseHas('inventory_transactions', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product1->id,
            'transaction_type' => 'ADJUSTMENT',
            'reference_type' => 'stock_adjustment',
            'reference_id' => $adjustment->id,
            'qty_before' => 50,
            'qty_change' => -5,
            'qty_after' => 45,
        ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product2->id,
            'transaction_type' => 'ADJUSTMENT',
            'reference_type' => 'stock_adjustment',
            'reference_id' => $adjustment->id,
            'qty_before' => 30,
            'qty_change' => 8,
            'qty_after' => 38,
        ]);
    }

    public function test_it_prevents_negative_adjusted_quantity(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('items', [
                [
                    'product_id' => $this->product1->id,
                    'adjusted_quantity' => -10, // Invalid: negative quantity
                ],
            ])
            ->call('save')
            ->assertHasErrors(['items.0.adjusted_quantity']);
    }

    public function test_validation_fails_when_creating_without_required_fields(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('warehouse_id', null)
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['warehouse_id' => 'required', 'items' => 'required']);
    }

    public function test_it_can_view_stock_adjustment_details(): void
    {
        $adjustment = StockAdjustment::create([
            'reference_no' => 'ADJ-DETAIL-VIEW',
            'warehouse_id' => $this->warehouse->id,
            'adjustment_date' => now()->toDateString(),
            'reason' => 'Cycle Count',
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        $adjustment->items()->create([
            'product_id' => $this->product1->id,
            'current_quantity' => 20,
            'adjusted_quantity' => 18,
            'quantity_change' => -2,
            'note' => 'Missing 2 units',
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('viewDetail', $adjustment->id)
            ->assertDispatched('open-modal', 'stock-adjustment-detail-modal')
            ->assertSet('viewStockAdjustmentId', $adjustment->id)
            ->assertSee('ADJ-DETAIL-VIEW')
            ->assertSee('Heavy Duty Stapler');
    }
}

