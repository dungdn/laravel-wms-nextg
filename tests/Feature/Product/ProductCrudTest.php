<?php

namespace Tests\Feature\Product;

use App\Livewire\Product\Index;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $category;

    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->category = Category::create([
            'code' => 'CAT_ELEC',
            'name' => 'Electronics',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUP_DELL',
            'name' => 'Dell Global',
            'is_active' => true,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('products.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_product_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('products.index'));

        $response->assertOk()
            ->assertSee('Products')
            ->assertSee('Add Product');
    }

    public function test_it_displays_products_list_and_search(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'LAPTOP-DELL-01',
            'name' => 'Dell XPS 15',
            'unit' => 'pcs',
            'cost_price' => 1200.00,
            'selling_price' => 1500.00,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'MOUSE-LOGI-01',
            'name' => 'Logitech MX Master 3',
            'unit' => 'pcs',
            'cost_price' => 70.00,
            'selling_price' => 99.00,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('LAPTOP-DELL-01')
            ->assertSee('Dell XPS 15')
            ->assertSee('MOUSE-LOGI-01')
            ->assertSee('Logitech MX Master 3')
            ->set('search', 'XPS')
            ->assertSee('LAPTOP-DELL-01')
            ->assertDontSee('MOUSE-LOGI-01');
    }

    public function test_it_can_create_a_product(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->assertDispatched('open-modal', 'product-form-modal')
            ->set('form.sku', 'IPHONE-15-PRO')
            ->set('form.name', 'iPhone 15 Pro')
            ->set('form.category_id', $this->category->id)
            ->set('form.supplier_id', $this->supplier->id)
            ->set('form.unit', 'box')
            ->set('form.cost_price', 900)
            ->set('form.selling_price', 1199)
            ->set('form.description', 'Flagship phone')
            ->set('form.is_active', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'product-form-modal')
            ->assertSee('iPhone 15 Pro');

        $this->assertDatabaseHas('products', [
            'sku' => 'IPHONE-15-PRO',
            'name' => 'iPhone 15 Pro',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'unit' => 'box',
            'cost_price' => 900.00,
            'selling_price' => 1199.00,
            'is_active' => 1,
        ]);
    }

    public function test_validation_fails_when_creating_without_required_fields(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('form.sku', '')
            ->set('form.name', '')
            ->set('form.unit', '')
            ->call('save')
            ->assertHasErrors(['form.sku' => 'required', 'form.name' => 'required', 'form.unit' => 'required']);
    }

    public function test_validation_fails_on_duplicate_sku(): void
    {
        Product::create([
            'sku' => 'SKU-EXISTS',
            'name' => 'Existing Product',
            'unit' => 'pcs',
            'cost_price' => 10,
            'selling_price' => 20,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('form.sku', 'SKU-EXISTS')
            ->set('form.name', 'Another Product')
            ->set('form.unit', 'pcs')
            ->set('form.cost_price', 15)
            ->set('form.selling_price', 25)
            ->call('save')
            ->assertHasErrors(['form.sku' => 'unique']);
    }

    public function test_it_can_load_product_for_editing(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'SKU-EDIT',
            'name' => 'Original Product',
            'unit' => 'pcs',
            'cost_price' => 50.00,
            'selling_price' => 80.00,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('edit', $product->id)
            ->assertDispatched('open-modal', 'product-form-modal')
            ->assertSet('form.sku', 'SKU-EDIT')
            ->assertSet('form.name', 'Original Product')
            ->assertSet('form.cost_price', 50.00)
            ->assertSet('form.selling_price', 80.00)
            ->assertSet('isEditing', true);
    }

    public function test_it_can_update_a_product_keeping_the_same_sku(): void
    {
        $product = Product::create([
            'sku' => 'SKU-KEEP',
            'name' => 'Old Product Name',
            'unit' => 'pcs',
            'cost_price' => 100.00,
            'selling_price' => 150.00,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('edit', $product->id)
            ->set('form.name', 'Updated Product Name')
            ->set('form.selling_price', 180.00)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'product-form-modal');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'sku' => 'SKU-KEEP',
            'name' => 'Updated Product Name',
            'selling_price' => 180.00,
        ]);
    }

    public function test_it_can_delete_a_product(): void
    {
        $product = Product::create([
            'sku' => 'SKU-DEL',
            'name' => 'Product to Delete',
            'unit' => 'pcs',
            'cost_price' => 10,
            'selling_price' => 20,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('delete', $product->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
