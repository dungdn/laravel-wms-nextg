<?php

namespace Tests\Feature\StockIn;

use App\Livewire\StockIn\Index;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockInCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Warehouse $warehouse;

    private Supplier $supplier;

    private Product $product1;

    private Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $category = Category::create([
            'code' => 'CAT_INBOUND',
            'name' => 'Inbound Goods',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUP_INBOUND',
            'name' => 'Inbound Logistics Supplier',
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'code' => 'WH_MAIN',
            'name' => 'Main Receiving Warehouse',
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'SKU-IN-01',
            'name' => 'Mechanical Keyboard',
            'unit' => 'pcs',
            'cost_price' => 45.00,
            'selling_price' => 70.00,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'SKU-IN-02',
            'name' => 'Wireless Mouse',
            'unit' => 'pcs',
            'cost_price' => 25.00,
            'selling_price' => 40.00,
            'is_active' => true,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('stock-ins.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_stock_in_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('stock-ins.index'));

        $response->assertOk()
            ->assertSee('Stock In')
            ->assertSee('New Stock In');
    }

    public function test_it_displays_stock_in_records_and_searches_by_reference(): void
    {
        $stockIn1 = StockIn::create([
            'reference_no' => 'IN-20261105-0001',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'transaction_date' => now()->toDateString(),
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        $stockIn2 = StockIn::create([
            'reference_no' => 'IN-20261105-0002',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'transaction_date' => now()->toDateString(),
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('IN-20261105-0001')
            ->assertSee('IN-20261105-0002')
            ->set('search', '0001')
            ->assertViewHas('stockIns', function ($stockIns) use ($stockIn1) {
                return $stockIns->total() === 1
                    && $stockIns->first()->id === $stockIn1->id;
            });
    }

    public function test_it_can_create_a_stock_in_voucher_with_multiple_items_and_updates_inventory_and_logs_transactions(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->assertDispatched('open-modal', 'stock-in-create-modal')
            ->set('reference_no', 'IN-TEST-9999')
            ->set('supplier_id', $this->supplier->id)
            ->set('warehouse_id', $this->warehouse->id)
            ->set('transaction_date', '2026-11-05')
            ->set('note', 'Initial container arrival')
            ->set('items', [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 50,
                    'unit_cost' => 45.00,
                ],
                [
                    'product_id' => $this->product2->id,
                    'quantity' => 30,
                    'unit_cost' => 25.00,
                ],
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'stock-in-create-modal');

        // 1. Verify Stock In Header
        $this->assertDatabaseHas('stock_ins', [
            'reference_no' => 'IN-TEST-9999',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'completed',
        ]);

        $stockIn = StockIn::where('reference_no', 'IN-TEST-9999')->first();

        // 2. Verify Stock In Items
        $this->assertDatabaseHas('stock_in_items', [
            'stock_in_id' => $stockIn->id,
            'product_id' => $this->product1->id,
            'quantity' => 50,
            'unit_cost' => 45.00,
        ]);

        $this->assertDatabaseHas('stock_in_items', [
            'stock_in_id' => $stockIn->id,
            'product_id' => $this->product2->id,
            'quantity' => 30,
            'unit_cost' => 25.00,
        ]);

        // 3. Verify Inventories Updated
        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product1->id,
            'quantity' => 50,
        ]);

        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product2->id,
            'quantity' => 30,
        ]);

        // 4. Verify Inventory Audit Transactions Logged
        $this->assertDatabaseHas('inventory_transactions', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product1->id,
            'transaction_type' => 'IN',
            'reference_type' => 'stock_in',
            'reference_id' => $stockIn->id,
            'qty_before' => 0,
            'qty_change' => 50,
            'qty_after' => 50,
        ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product2->id,
            'transaction_type' => 'IN',
            'reference_type' => 'stock_in',
            'reference_id' => $stockIn->id,
            'qty_before' => 0,
            'qty_change' => 30,
            'qty_after' => 30,
        ]);
    }

    public function test_it_increments_existing_inventory_quantity(): void
    {
        // Pre-existing inventory
        Inventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product1->id,
            'quantity' => 20,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('reference_no', 'IN-INCREMENT-01')
            ->set('supplier_id', $this->supplier->id)
            ->set('warehouse_id', $this->warehouse->id)
            ->set('transaction_date', '2026-11-05')
            ->set('items', [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 15,
                    'unit_cost' => 45.00,
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        // 20 + 15 = 35
        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product1->id,
            'quantity' => 35,
        ]);

        $stockIn = StockIn::where('reference_no', 'IN-INCREMENT-01')->first();

        $this->assertDatabaseHas('inventory_transactions', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product1->id,
            'transaction_type' => 'IN',
            'reference_id' => $stockIn->id,
            'qty_before' => 20,
            'qty_change' => 15,
            'qty_after' => 35,
        ]);
    }

    public function test_validation_fails_when_creating_without_required_fields(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('supplier_id', null)
            ->set('warehouse_id', null)
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['supplier_id' => 'required', 'warehouse_id' => 'required', 'items' => 'required']);
    }

    public function test_it_can_view_stock_in_details(): void
    {
        $stockIn = StockIn::create([
            'reference_no' => 'IN-DETAIL-VIEW',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'transaction_date' => now()->toDateString(),
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        $stockIn->items()->create([
            'product_id' => $this->product1->id,
            'quantity' => 10,
            'unit_cost' => 45.00,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('viewDetail', $stockIn->id)
            ->assertDispatched('open-modal', 'stock-in-detail-modal')
            ->assertSet('viewStockInId', $stockIn->id)
            ->assertSee('IN-DETAIL-VIEW')
            ->assertSee('Mechanical Keyboard');
    }
}
