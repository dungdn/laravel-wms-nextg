<?php

namespace Tests\Feature\PurchaseOrder;

use App\Livewire\PurchaseOrder\Index;
use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseOrderCrudTest extends TestCase
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
            'code' => 'CAT_PROCURE',
            'name' => 'Procurement Category',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUP_GLOBAL',
            'name' => 'Global Tech Supplier',
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'code' => 'WH_PROCURE',
            'name' => 'Central Procurement Warehouse',
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'SKU-PO-01',
            'name' => 'Server Motherboard',
            'unit' => 'pcs',
            'cost_price' => 320.00,
            'selling_price' => 450.00,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'SKU-PO-02',
            'name' => 'ECC Memory 32GB',
            'unit' => 'pcs',
            'cost_price' => 95.00,
            'selling_price' => 140.00,
            'is_active' => true,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('purchase-orders.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_purchase_order_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase-orders.index'));

        $response->assertOk()
            ->assertSee('Purchase Orders')
            ->assertSee('New Purchase Order');
    }

    public function test_it_displays_purchase_order_records_and_searches_by_po_number(): void
    {
        $po1 = PurchaseOrder::create([
            'po_number' => 'PO-20261106-0001',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $po2 = PurchaseOrder::create([
            'po_number' => 'PO-20261106-0002',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('PO-20261106-0001')
            ->assertSee('PO-20261106-0002')
            ->set('search', '0001')
            ->assertViewHas('purchaseOrders', function ($pos) use ($po1) {
                return $pos->total() === 1
                    && $pos->first()->id === $po1->id;
            });
    }

    public function test_it_can_create_a_purchase_order_with_multiple_items(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->assertDispatched('open-modal', 'purchase-order-create-modal')
            ->set('po_number', 'PO-CUSTOM-7777')
            ->set('supplier_id', $this->supplier->id)
            ->set('warehouse_id', $this->warehouse->id)
            ->set('order_date', '2026-11-06')
            ->set('expected_delivery_date', '2026-11-20')
            ->set('status', 'pending')
            ->set('note', 'Q4 Bulk Procurement Order')
            ->set('items', [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 25,
                    'unit_cost' => 320.00,
                ],
                [
                    'product_id' => $this->product2->id,
                    'quantity' => 50,
                    'unit_cost' => 95.00,
                ],
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'purchase-order-create-modal');

        // 1. Verify Purchase Order Header
        $this->assertDatabaseHas('purchase_orders', [
            'po_number' => 'PO-CUSTOM-7777',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
            'note' => 'Q4 Bulk Procurement Order',
        ]);

        $po = PurchaseOrder::where('po_number', 'PO-CUSTOM-7777')->first();
        $this->assertNotNull($po);
        $this->assertEquals('2026-11-06', $po->order_date->format('Y-m-d'));
        $this->assertEquals('2026-11-20', $po->expected_delivery_date?->format('Y-m-d'));

        // 2. Verify Line Items
        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'product_id' => $this->product1->id,
            'quantity' => 25,
            'unit_cost' => 320.00,
            'received_quantity' => 0,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'product_id' => $this->product2->id,
            'quantity' => 50,
            'unit_cost' => 95.00,
            'received_quantity' => 0,
        ]);

        $this->assertEquals(75, $po->total_quantity);
        $this->assertEquals(12750.00, $po->total_amount); // 25*320 + 50*95 = 8000 + 4750 = 12750
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

    public function test_validation_fails_when_expected_delivery_date_is_before_order_date(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('supplier_id', $this->supplier->id)
            ->set('warehouse_id', $this->warehouse->id)
            ->set('order_date', '2026-11-10')
            ->set('expected_delivery_date', '2026-11-05') // Invalid: before order_date
            ->set('items', [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 5,
                    'unit_cost' => 320.00,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['expected_delivery_date']);
    }

    public function test_it_can_view_purchase_order_details(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-DETAIL-VIEW',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $po->items()->create([
            'product_id' => $this->product1->id,
            'quantity' => 10,
            'unit_cost' => 320.00,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('viewDetail', $po->id)
            ->assertDispatched('open-modal', 'purchase-order-detail-modal')
            ->assertSet('viewPurchaseOrderId', $po->id)
            ->assertSee('PO-DETAIL-VIEW')
            ->assertSee('Server Motherboard');
    }
}
