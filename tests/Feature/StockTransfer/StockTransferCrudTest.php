<?php

namespace Tests\Feature\StockTransfer;

use App\Livewire\StockTransfer\Index;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockTransferCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Warehouse $fromWarehouse;

    private Warehouse $toWarehouse;

    private Product $product1;

    private Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $category = Category::create([
            'code' => 'CAT_TRANSFER',
            'name' => 'Transfer Category',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'code' => 'SUP_TRANSFER',
            'name' => 'Transfer Supplier',
            'is_active' => true,
        ]);

        $this->fromWarehouse = Warehouse::create([
            'code' => 'WH_NORTH',
            'name' => 'North Distribution Center',
            'is_active' => true,
        ]);

        $this->toWarehouse = Warehouse::create([
            'code' => 'WH_SOUTH',
            'name' => 'South Fulfillment Hub',
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'SKU-TR-01',
            'name' => 'Industrial Fan',
            'unit' => 'pcs',
            'cost_price' => 80.00,
            'selling_price' => 120.00,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'SKU-TR-02',
            'name' => 'LED Floodlight',
            'unit' => 'pcs',
            'cost_price' => 35.00,
            'selling_price' => 60.00,
            'is_active' => true,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('stock-transfers.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_stock_transfer_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('stock-transfers.index'));

        $response->assertOk()
            ->assertSee('Stock Transfers')
            ->assertSee('New Transfer');
    }

    public function test_it_displays_stock_transfer_records_and_searches_by_reference(): void
    {
        $transfer1 = StockTransfer::create([
            'reference_no' => 'TR-20261106-0001',
            'from_warehouse_id' => $this->fromWarehouse->id,
            'to_warehouse_id' => $this->toWarehouse->id,
            'transfer_date' => now()->toDateString(),
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        $transfer2 = StockTransfer::create([
            'reference_no' => 'TR-20261106-0002',
            'from_warehouse_id' => $this->fromWarehouse->id,
            'to_warehouse_id' => $this->toWarehouse->id,
            'transfer_date' => now()->toDateString(),
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('TR-20261106-0001')
            ->assertSee('TR-20261106-0002')
            ->set('search', '0001')
            ->assertViewHas('stockTransfers', function ($transfers) use ($transfer1) {
                return $transfers->total() === 1
                    && $transfers->first()->id === $transfer1->id;
            });
    }

    public function test_it_can_create_a_stock_transfer_with_multiple_items_and_updates_both_warehouses_and_logs_audit_transactions(): void
    {
        // Initial inventory at source
        Inventory::create([
            'warehouse_id' => $this->fromWarehouse->id,
            'product_id' => $this->product1->id,
            'quantity' => 50,
        ]);

        Inventory::create([
            'warehouse_id' => $this->fromWarehouse->id,
            'product_id' => $this->product2->id,
            'quantity' => 30,
        ]);

        // Initial inventory at destination (product1 has 10, product2 has 0)
        Inventory::create([
            'warehouse_id' => $this->toWarehouse->id,
            'product_id' => $this->product1->id,
            'quantity' => 10,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->assertDispatched('open-modal', 'stock-transfer-create-modal')
            ->set('reference_no', 'TR-TEST-6666')
            ->set('from_warehouse_id', $this->fromWarehouse->id)
            ->set('to_warehouse_id', $this->toWarehouse->id)
            ->set('transfer_date', '2026-11-06')
            ->set('note', 'Seasonal stock balancing')
            ->set('items', [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 20,
                ],
                [
                    'product_id' => $this->product2->id,
                    'quantity' => 15,
                ],
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'stock-transfer-create-modal');

        // 1. Verify Stock Transfer Header
        $this->assertDatabaseHas('stock_transfers', [
            'reference_no' => 'TR-TEST-6666',
            'from_warehouse_id' => $this->fromWarehouse->id,
            'to_warehouse_id' => $this->toWarehouse->id,
            'status' => 'completed',
            'note' => 'Seasonal stock balancing',
        ]);

        $transfer = StockTransfer::where('reference_no', 'TR-TEST-6666')->first();

        // 2. Verify Transfer Line Items
        $this->assertDatabaseHas('stock_transfer_items', [
            'stock_transfer_id' => $transfer->id,
            'product_id' => $this->product1->id,
            'quantity' => 20,
        ]);

        $this->assertDatabaseHas('stock_transfer_items', [
            'stock_transfer_id' => $transfer->id,
            'product_id' => $this->product2->id,
            'quantity' => 15,
        ]);

        // 3. Verify Source Inventory Deducted (50 - 20 = 30, 30 - 15 = 15)
        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->fromWarehouse->id,
            'product_id' => $this->product1->id,
            'quantity' => 30,
        ]);

        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->fromWarehouse->id,
            'product_id' => $this->product2->id,
            'quantity' => 15,
        ]);

        // 4. Verify Destination Inventory Incremented (10 + 20 = 30, 0 + 15 = 15)
        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->toWarehouse->id,
            'product_id' => $this->product1->id,
            'quantity' => 30,
        ]);

        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->toWarehouse->id,
            'product_id' => $this->product2->id,
            'quantity' => 15,
        ]);

        // 5. Verify Dual Audit Logging in inventory_transactions
        // Source Outbound
        $this->assertDatabaseHas('inventory_transactions', [
            'warehouse_id' => $this->fromWarehouse->id,
            'product_id' => $this->product1->id,
            'transaction_type' => 'TRANSFER_OUT',
            'reference_type' => 'stock_transfer',
            'reference_id' => $transfer->id,
            'qty_before' => 50,
            'qty_change' => -20,
            'qty_after' => 30,
        ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'warehouse_id' => $this->fromWarehouse->id,
            'product_id' => $this->product2->id,
            'transaction_type' => 'TRANSFER_OUT',
            'reference_type' => 'stock_transfer',
            'reference_id' => $transfer->id,
            'qty_before' => 30,
            'qty_change' => -15,
            'qty_after' => 15,
        ]);

        // Destination Inbound
        $this->assertDatabaseHas('inventory_transactions', [
            'warehouse_id' => $this->toWarehouse->id,
            'product_id' => $this->product1->id,
            'transaction_type' => 'TRANSFER_IN',
            'reference_type' => 'stock_transfer',
            'reference_id' => $transfer->id,
            'qty_before' => 10,
            'qty_change' => 20,
            'qty_after' => 30,
        ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'warehouse_id' => $this->toWarehouse->id,
            'product_id' => $this->product2->id,
            'transaction_type' => 'TRANSFER_IN',
            'reference_type' => 'stock_transfer',
            'reference_id' => $transfer->id,
            'qty_before' => 0,
            'qty_change' => 15,
            'qty_after' => 15,
        ]);
    }

    public function test_it_prevents_transfer_when_destination_warehouse_is_identical_to_source_warehouse(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('from_warehouse_id', $this->fromWarehouse->id)
            ->set('to_warehouse_id', $this->fromWarehouse->id) // Same warehouse
            ->set('items', [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 5,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['to_warehouse_id']);
    }

    public function test_it_prevents_negative_stock_and_throws_validation_error_when_source_inventory_is_insufficient(): void
    {
        // Only 5 in stock at source warehouse
        Inventory::create([
            'warehouse_id' => $this->fromWarehouse->id,
            'product_id' => $this->product1->id,
            'quantity' => 5,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('reference_no', 'TR-FAIL-01')
            ->set('from_warehouse_id', $this->fromWarehouse->id)
            ->set('to_warehouse_id', $this->toWarehouse->id)
            ->set('items', [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 10, // Requesting 10 while only 5 available
                ],
            ])
            ->call('save')
            ->assertHasErrors(['items']);

        // Assert no transfer was created
        $this->assertDatabaseMissing('stock_transfers', [
            'reference_no' => 'TR-FAIL-01',
        ]);

        // Assert inventory at source warehouse was not changed
        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->fromWarehouse->id,
            'product_id' => $this->product1->id,
            'quantity' => 5,
        ]);
    }

    public function test_validation_fails_when_creating_without_required_fields(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('from_warehouse_id', null)
            ->set('to_warehouse_id', null)
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['from_warehouse_id' => 'required', 'to_warehouse_id' => 'required', 'items' => 'required']);
    }

    public function test_it_can_view_stock_transfer_details(): void
    {
        $transfer = StockTransfer::create([
            'reference_no' => 'TR-DETAIL-VIEW',
            'from_warehouse_id' => $this->fromWarehouse->id,
            'to_warehouse_id' => $this->toWarehouse->id,
            'transfer_date' => now()->toDateString(),
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        $transfer->items()->create([
            'product_id' => $this->product1->id,
            'quantity' => 12,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('viewDetail', $transfer->id)
            ->assertDispatched('open-modal', 'stock-transfer-detail-modal')
            ->assertSet('viewStockTransferId', $transfer->id)
            ->assertSee('TR-DETAIL-VIEW')
            ->assertSee('Industrial Fan');
    }
}
