<?php

namespace Tests\Feature\Inventory;

use App\Livewire\Inventory\Index;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryCrudTest extends TestCase
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
            'code' => 'CAT_HARDWARE',
            'name' => 'Computer Hardware',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'code' => 'SUP_GLOBAL',
            'name' => 'Global Supplier',
            'is_active' => true,
        ]);

        $this->warehouse1 = Warehouse::create([
            'code' => 'WH-SOUTH',
            'name' => 'Southern Hub',
            'is_active' => true,
        ]);

        $this->warehouse2 = Warehouse::create([
            'code' => 'WH-NORTH',
            'name' => 'Northern Hub',
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'SKU-CPU-I9',
            'name' => 'Intel Core i9 14900K',
            'unit' => 'pcs',
            'cost_price' => 500,
            'selling_price' => 650,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'SKU-GPU-4090',
            'name' => 'NVIDIA RTX 4090',
            'unit' => 'pcs',
            'cost_price' => 1600,
            'selling_price' => 2000,
            'is_active' => true,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('inventory.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_inventory_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('inventory.index'));

        $response->assertOk()
            ->assertSee('Inventory')
            ->assertSee('Adjust Stock');
    }

    public function test_it_displays_inventory_records_and_filters_by_warehouse(): void
    {
        Inventory::create([
            'warehouse_id' => $this->warehouse1->id,
            'product_id' => $this->product1->id,
            'quantity' => 100,
        ]);

        Inventory::create([
            'warehouse_id' => $this->warehouse2->id,
            'product_id' => $this->product2->id,
            'quantity' => 5,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('Southern Hub')
            ->assertSee('Northern Hub')
            ->assertSee('In Stock')
            ->assertSee('Low Stock')
            ->set('warehouseFilter', $this->warehouse1->id)
            ->assertViewHas('inventories', function ($inventories) {
                return $inventories->total() === 1
                    && $inventories->first()->product_id === $this->product1->id;
            });
    }

    public function test_it_searches_inventory_by_product_sku_or_name(): void
    {
        Inventory::create([
            'warehouse_id' => $this->warehouse1->id,
            'product_id' => $this->product1->id,
            'quantity' => 50,
        ]);

        Inventory::create([
            'warehouse_id' => $this->warehouse1->id,
            'product_id' => $this->product2->id,
            'quantity' => 20,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('search', '4090')
            ->assertViewHas('inventories', function ($inventories) {
                return $inventories->total() === 1
                    && $inventories->first()->product_id === $this->product2->id;
            });
    }

    public function test_it_can_create_or_adjust_inventory_record(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->assertDispatched('open-modal', 'inventory-form-modal')
            ->set('form.warehouse_id', $this->warehouse1->id)
            ->set('form.product_id', $this->product1->id)
            ->set('form.quantity', 75)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'inventory-form-modal');

        $this->assertDatabaseHas('inventories', [
            'warehouse_id' => $this->warehouse1->id,
            'product_id' => $this->product1->id,
            'quantity' => 75,
        ]);
    }

    public function test_validation_fails_when_creating_without_required_fields(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('form.warehouse_id', null)
            ->set('form.product_id', null)
            ->call('save')
            ->assertHasErrors(['form.warehouse_id' => 'required', 'form.product_id' => 'required']);
    }

    public function test_it_can_load_inventory_for_editing(): void
    {
        $inventory = Inventory::create([
            'warehouse_id' => $this->warehouse1->id,
            'product_id' => $this->product1->id,
            'quantity' => 15,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('edit', $inventory->id)
            ->assertDispatched('open-modal', 'inventory-form-modal')
            ->assertSet('form.warehouse_id', $this->warehouse1->id)
            ->assertSet('form.product_id', $this->product1->id)
            ->assertSet('form.quantity', 15)
            ->assertSet('isEditing', true);
    }

    public function test_it_can_update_inventory_quantity(): void
    {
        $inventory = Inventory::create([
            'warehouse_id' => $this->warehouse1->id,
            'product_id' => $this->product1->id,
            'quantity' => 30,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('edit', $inventory->id)
            ->set('form.quantity', 88)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'inventory-form-modal');

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'quantity' => 88,
        ]);
    }

    public function test_it_can_delete_inventory_record(): void
    {
        $inventory = Inventory::create([
            'warehouse_id' => $this->warehouse1->id,
            'product_id' => $this->product1->id,
            'quantity' => 10,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('delete', $inventory->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('inventories', [
            'id' => $inventory->id,
        ]);
    }
}
