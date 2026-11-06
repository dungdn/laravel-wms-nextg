<?php

namespace Tests\Feature\Report;

use App\Livewire\Report\Index;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Warehouse $warehouse1;

    private Warehouse $warehouse2;

    private Product $product1;

    private Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $category = Category::create([
            'code' => 'CAT_REPORT',
            'name' => 'Report Analytics Category',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'code' => 'SUP_REPORT',
            'name' => 'Report Analytics Supplier',
            'is_active' => true,
        ]);

        $this->warehouse1 = Warehouse::create([
            'code' => 'WH_EAST',
            'name' => 'East Coast Warehouse',
            'is_active' => true,
        ]);

        $this->warehouse2 = Warehouse::create([
            'code' => 'WH_WEST',
            'name' => 'West Coast Warehouse',
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'SKU-RPT-01',
            'name' => 'Laser Barcode Scanner',
            'unit' => 'pcs',
            'cost_price' => 50.00,
            'selling_price' => 90.00,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'SKU-RPT-02',
            'name' => 'Label Printer Roll',
            'unit' => 'roll',
            'cost_price' => 10.00,
            'selling_price' => 18.00,
            'is_active' => true,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_reports_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertOk()
            ->assertSee('Inventory Audit Log')
            ->assertSee('Warehouse Stock Breakdown');
    }

    public function test_it_calculates_executive_kpi_metrics_accurately(): void
    {
        // Setup inventory:
        // WH1: product1 = 20 units ($50/unit = $1000)
        // WH1: product2 = 5 units ($10/unit = $50) -> Low stock (<= 10)
        // WH2: product2 = 0 units ($10/unit = $0) -> Out of stock (<= 0)
        Inventory::create([
            'warehouse_id' => $this->warehouse1->id,
            'product_id' => $this->product1->id,
            'quantity' => 20,
        ]);

        Inventory::create([
            'warehouse_id' => $this->warehouse1->id,
            'product_id' => $this->product2->id,
            'quantity' => 5,
        ]);

        Inventory::create([
            'warehouse_id' => $this->warehouse2->id,
            'product_id' => $this->product2->id,
            'quantity' => 0,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertViewHas('metrics', function ($metrics) {
                // Total products in catalog = 2
                // Total units across all inventories = 20 + 5 + 0 = 25
                // Total valuation = 20*50 + 5*10 + 0*10 = 1000 + 50 = 1050
                // Low stock (quantity > 0 and <= 10) = 1 (product2 in WH1)
                // Out of stock (quantity <= 0) = 1 (product2 in WH2)
                return $metrics['total_products'] === 2
                    && $metrics['total_units'] === 25
                    && $metrics['total_valuation'] == 1050.00
                    && $metrics['low_stock_count'] === 1
                    && $metrics['out_of_stock_count'] === 1;
            });
    }

    public function test_it_displays_inventory_audit_log_transactions_and_filters_by_type(): void
    {
        $txIn = InventoryTransaction::create([
            'warehouse_id' => $this->warehouse1->id,
            'product_id' => $this->product1->id,
            'transaction_type' => 'IN',
            'reference_type' => 'stock_in',
            'reference_id' => 1,
            'qty_before' => 0,
            'qty_change' => 50,
            'qty_after' => 50,
            'created_by' => $this->user->id,
        ]);

        $txOut = InventoryTransaction::create([
            'warehouse_id' => $this->warehouse1->id,
            'product_id' => $this->product1->id,
            'transaction_type' => 'OUT',
            'reference_type' => 'stock_out',
            'reference_id' => 1,
            'qty_before' => 50,
            'qty_change' => -10,
            'qty_after' => 40,
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('Stock In')
            ->assertSee('Stock Out')
            ->set('typeFilter', 'IN')
            ->assertViewHas('auditTrail', function ($auditTrail) use ($txIn) {
                return $auditTrail->total() === 1
                    && $auditTrail->first()->id === $txIn->id;
            });
    }

    public function test_it_filters_audit_log_by_warehouse_and_product(): void
    {
        $tx1 = InventoryTransaction::create([
            'warehouse_id' => $this->warehouse1->id,
            'product_id' => $this->product1->id,
            'transaction_type' => 'IN',
            'reference_type' => 'stock_in',
            'reference_id' => 1,
            'qty_before' => 0,
            'qty_change' => 20,
            'qty_after' => 20,
            'created_by' => $this->user->id,
        ]);

        $tx2 = InventoryTransaction::create([
            'warehouse_id' => $this->warehouse2->id,
            'product_id' => $this->product2->id,
            'transaction_type' => 'IN',
            'reference_type' => 'stock_in',
            'reference_id' => 2,
            'qty_before' => 0,
            'qty_change' => 15,
            'qty_after' => 15,
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('warehouseFilter', $this->warehouse1->id)
            ->set('productFilter', $this->product1->id)
            ->assertViewHas('auditTrail', function ($auditTrail) use ($tx1) {
                return $auditTrail->total() === 1
                    && $auditTrail->first()->id === $tx1->id;
            });
    }

    public function test_it_can_switch_tabs_to_warehouse_stock_breakdown(): void
    {
        Inventory::create([
            'warehouse_id' => $this->warehouse1->id,
            'product_id' => $this->product1->id,
            'quantity' => 100,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSet('activeTab', 'audit')
            ->call('setTab', 'warehouse')
            ->assertSet('activeTab', 'warehouse')
            ->assertSee('East Coast Warehouse')
            ->assertSee('WH_EAST');
    }

    public function test_it_can_reset_filters(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('warehouseFilter', $this->warehouse1->id)
            ->set('productFilter', $this->product1->id)
            ->set('typeFilter', 'IN')
            ->set('fromDate', '2026-11-01')
            ->set('toDate', '2026-11-06')
            ->call('resetFilters')
            ->assertSet('warehouseFilter', null)
            ->assertSet('productFilter', null)
            ->assertSet('typeFilter', null)
            ->assertSet('fromDate', null)
            ->assertSet('toDate', null);
    }
}
