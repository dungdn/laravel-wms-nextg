<?php

namespace Tests\Feature\Category;

use App\Livewire\Category\Index;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('categories.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_category_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('categories.index'));

        $response->assertOk()
            ->assertSee('Categories')
            ->assertSee('Add Category');
    }

    public function test_it_displays_categories_list_and_search(): void
    {
        Category::create([
            'code' => 'CAT001',
            'name' => 'Laptops',
            'is_active' => true,
        ]);

        Category::create([
            'code' => 'CAT002',
            'name' => 'Keyboards',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('CAT001')
            ->assertSee('Laptops')
            ->assertSee('CAT002')
            ->assertSee('Keyboards')
            ->set('search', 'Laptop')
            ->assertSee('CAT001')
            ->assertDontSee('CAT002');
    }

    public function test_it_can_create_a_category(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->assertDispatched('open-modal', 'category-form-modal')
            ->set('form.code', 'CAT_NEW')
            ->set('form.name', 'Monitors')
            ->set('form.description', 'Display devices')
            ->set('form.is_active', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'category-form-modal')
            ->assertSee('Monitors');

        $this->assertDatabaseHas('categories', [
            'code' => 'CAT_NEW',
            'name' => 'Monitors',
            'description' => 'Display devices',
            'is_active' => 1,
        ]);
    }

    public function test_validation_fails_when_creating_without_required_fields(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('form.code', '')
            ->set('form.name', '')
            ->call('save')
            ->assertHasErrors(['form.code' => 'required', 'form.name' => 'required']);
    }

    public function test_validation_fails_on_duplicate_code(): void
    {
        Category::create([
            'code' => 'CAT_EXISTS',
            'name' => 'Existing Category',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('form.code', 'CAT_EXISTS')
            ->set('form.name', 'Another Category')
            ->call('save')
            ->assertHasErrors(['form.code' => 'unique']);
    }

    public function test_it_can_load_category_for_editing(): void
    {
        $category = Category::create([
            'code' => 'CAT_EDIT',
            'name' => 'Original Category',
            'description' => 'Original Description',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('edit', $category->id)
            ->assertDispatched('open-modal', 'category-form-modal')
            ->assertSet('form.code', 'CAT_EDIT')
            ->assertSet('form.name', 'Original Category')
            ->assertSet('form.description', 'Original Description')
            ->assertSet('isEditing', true);
    }

    public function test_it_can_update_a_category_keeping_the_same_code(): void
    {
        $category = Category::create([
            'code' => 'CAT_KEEP',
            'name' => 'Old Category Name',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('edit', $category->id)
            ->set('form.name', 'Updated Category Name')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'category-form-modal');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'code' => 'CAT_KEEP',
            'name' => 'Updated Category Name',
        ]);
    }

    public function test_it_can_delete_a_category(): void
    {
        $category = Category::create([
            'code' => 'CAT_DEL',
            'name' => 'Category to Delete',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('delete', $category->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }
}
