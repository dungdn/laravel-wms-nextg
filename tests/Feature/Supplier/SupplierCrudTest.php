<?php

namespace Tests\Feature\Supplier;

use App\Livewire\Supplier\Index;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierCrudTest extends TestCase
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
        $response = $this->get(route('suppliers.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_supplier_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('suppliers.index'));

        $response->assertOk()
            ->assertSee('Suppliers')
            ->assertSee('Add Supplier');
    }

    public function test_it_displays_suppliers_list_and_search(): void
    {
        Supplier::create([
            'code' => 'SUP001',
            'name' => 'Dell Technologies',
            'is_active' => true,
        ]);

        Supplier::create([
            'code' => 'SUP002',
            'name' => 'HP Enterprise',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('SUP001')
            ->assertSee('Dell Technologies')
            ->assertSee('SUP002')
            ->assertSee('HP Enterprise')
            ->set('search', 'Dell')
            ->assertSee('SUP001')
            ->assertDontSee('SUP002');
    }

    public function test_it_can_create_a_supplier(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->assertDispatched('open-modal', 'supplier-form-modal')
            ->set('form.code', 'SUP_NEW')
            ->set('form.name', 'New Technology Corp')
            ->set('form.contact_name', 'Alice Smith')
            ->set('form.phone', '0123456789')
            ->set('form.email', 'alice@newtech.com')
            ->set('form.address', '123 Silicon Alley')
            ->set('form.is_active', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'supplier-form-modal')
            ->assertSee('New Technology Corp');

        $this->assertDatabaseHas('suppliers', [
            'code' => 'SUP_NEW',
            'name' => 'New Technology Corp',
            'email' => 'alice@newtech.com',
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
        Supplier::create([
            'code' => 'SUP_EXISTS',
            'name' => 'Existing Supplier',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('form.code', 'SUP_EXISTS')
            ->set('form.name', 'Another Supplier')
            ->call('save')
            ->assertHasErrors(['form.code' => 'unique']);
    }

    public function test_it_can_load_supplier_for_editing(): void
    {
        $supplier = Supplier::create([
            'code' => 'SUP_EDIT',
            'name' => 'Original Name',
            'contact_name' => 'Bob',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('edit', $supplier->id)
            ->assertDispatched('open-modal', 'supplier-form-modal')
            ->assertSet('form.code', 'SUP_EDIT')
            ->assertSet('form.name', 'Original Name')
            ->assertSet('form.contact_name', 'Bob')
            ->assertSet('isEditing', true);
    }

    public function test_it_can_update_a_supplier_keeping_the_same_code(): void
    {
        $supplier = Supplier::create([
            'code' => 'SUP_KEEP',
            'name' => 'Old Name',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('edit', $supplier->id)
            ->set('form.name', 'Updated Supplier Name')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'supplier-form-modal');

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'code' => 'SUP_KEEP',
            'name' => 'Updated Supplier Name',
        ]);
    }

    public function test_it_can_delete_a_supplier(): void
    {
        $supplier = Supplier::create([
            'code' => 'SUP_DEL',
            'name' => 'Supplier to Delete',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('delete', $supplier->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id,
        ]);
    }
}
