<?php

namespace Tests\Feature\StockOut;

use App\Livewire\StockOut\Index;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockOut;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockOutCrudTest extends TestCase
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
            'code' => 'CAT_OUTBOUND',
            'name' => 'Outbound Electronics',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUP_OUTBOUND',
            'name' => 'Outbound Logistics Supplier',
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'code' => 'WH_OUT',
            'name' => 'Main Dispatch Warehouse',
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'SKU-OUT-01',
            'name' => 'Gaming Monitor',
            'unit' => 'pcs',
            'cost_price' => 150.00,
            'selling_price' => 250.00,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'SKU-OUT-02',
            'name' => 'USB-C Cable',
            'unit' => 'pcs',
            'cost_price' => 5.00,
            'selling_price' => 12.00,
            'is_active' => true,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('stock-outs.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_stock_out_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('stock-outs.index'));

        $response->assertOk()
            ->assertSee('Stock Out')
            ->assertSee('New Stock Out');
    }

    public function test_it_displays_stock_out_records_and_searches_by_reference(): void
    {
        $stockOut1 = StockOut::create([
            'reference_no' => 'OUT-20261106-0001',
            'warehouse_id' => $this->warehouse->id,
            'transaction_date' => now()->toDateString(),
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        $stockOut2 = StockOut::create([
            'reference_no' => 'OUT-20261106-0002',
            'warehouse_id' => $this->warehouse->id,
            'transaction_date' => now()->toDateString(),
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('OUT-20261106-0001')
            ->assertSee('OUT-20261106-0002')
            ->set('search', '0001')
            ->assertViewHas('stockOuts', function ($stockOuts) use ($stockOut1) {
                return $stockOuts->total() === 1
                    && $stockOuts->first()->id === $stockOut1->id;
            });
    }

    public function test_it_can_create_a_stock_out_voucher_with_multiple_items_and_deducts_inventory_and_logs_transactions(): void
    {
        // 1. Setup pre-existing inventory
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
            ->assertDispatched('open-modal', 'stock-out-create-modal')
            ->set('reference_no', 'OUT-TEST-8888')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('transaction_date', '2026-11-06')
            ->set('note', 'B2B order dispatch')
            ->set('items', [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 20,
                    'unit_price' => 250.00,
                ],
                [
                    'product_id' => $this->product2->id,
                    'quantity' => 10,
                    'unit_price' => 12.00,
                ],
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'stock-out-create-modal');

        // 2. Verify Stock Out Header
        $this->assertDatabaseHas('stock_outs', [
            'reference_no' => 'OUT-TEST-8888',
            'warehouse_id' => $this->warehouse->id,
            'status' => 'completed',
        ]);

        $stockOut = StockOut::where('reference_no', 'OUT-TEST-8888')->first();

        // 3. Verify Stock Out Items
        $this->assertDatabaseHas('stock_out_items', [
            'stock_out_id' => $stockOut->id,
            'product_id' => $this->product1->id,
            'quantity' => 20,
            'unit_price' => 250.00,
        ]);

        $this->assertDatabaseHas('stock_out_items', [
            'stock_out_id' => $stockOut->id,
            'product_id' => $this->product2->id,
            'quantity' => 10,
            'unit_price' => 12.00,
        ]);

        // 4. Verify Inventories Deducted (50 - 20 = 30, 30 - 10 = 20)
        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product1->id,
            'quantity' => 30,
        ]);

        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product2->id,
            'quantity' => 20,
        ]);

        // 5. Verify Inventory Audit Transactions Logged with Negative Change
        $this->assertDatabaseHas('inventory_transactions', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product1->id,
            'transaction_type' => 'OUT',
            'reference_type' => 'stock_out',
            'reference_id' => $stockOut->id,
            'qty_before' => 50,
            'qty_change' => -20,
            'qty_after' => 30,
        ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product2->id,
            'transaction_type' => 'OUT',
            'reference_type' => 'stock_out',
            'reference_id' => $stockOut->id,
            'qty_before' => 30,
            'qty_change' => -10,
            'qty_after' => 20,
        ]);
    }

    public function test_it_prevents_negative_stock_and_throws_validation_error_when_inventory_is_insufficient(): void
    {
        // Only 5 in stock
        Inventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product1->id,
            'quantity' => 5,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('reference_no', 'OUT-FAIL-01')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('transaction_date', '2026-11-06')
            ->set('items', [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 10, // Requesting 10 while only 5 available
                    'unit_price' => 250.00,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['items']);

        // Assert no stock out was created
        $this->assertDatabaseMissing('stock_outs', [
            'reference_no' => 'OUT-FAIL-01',
        ]);

        // Assert inventory was not changed
        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product1->id,
            'quantity' => 5,
        ]);
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

    public function test_it_can_view_stock_out_details(): void
    {
        $stockOut = StockOut::create([
            'reference_no' => 'OUT-DETAIL-VIEW',
            'warehouse_id' => $this->warehouse->id,
            'transaction_date' => now()->toDateString(),
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        $stockOut->items()->create([
            'product_id' => $this->product1->id,
            'quantity' => 15,
            'unit_price' => 250.00,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('viewDetail', $stockOut->id)
            ->assertDispatched('open-modal', 'stock-out-detail-modal')
            ->assertSet('viewStockOutId', $stockOut->id)
            ->assertSee('OUT-DETAIL-VIEW')
            ->assertSee('Gaming Monitor');
    }
}
