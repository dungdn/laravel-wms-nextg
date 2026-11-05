<?php

namespace Tests\Feature\Warehouse;

use App\Livewire\Warehouse\Index;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WarehouseCrudTest extends TestCase
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
        $response = $this->get(route('warehouses.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_warehouse_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('warehouses.index'));

        $response->assertOk()
            ->assertSee('Warehouses')
            ->assertSee('Add Warehouse');
    }

    public function test_it_displays_warehouses_list_and_search(): void
    {
        Warehouse::create([
            'code' => 'WH-HCM',
            'name' => 'Ho Chi Minh Central Warehouse',
            'address' => 'District 9, HCMC',
            'is_active' => true,
        ]);

        Warehouse::create([
            'code' => 'WH-HN',
            'name' => 'Ha Noi Northern Warehouse',
            'address' => 'Long Bien, Ha Noi',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('WH-HCM')
            ->assertSee('Ho Chi Minh Central Warehouse')
            ->assertSee('WH-HN')
            ->assertSee('Ha Noi Northern Warehouse')
            ->set('search', 'Northern')
            ->assertSee('Ha Noi Northern Warehouse')
            ->assertDontSee('Ho Chi Minh Central Warehouse');
    }

    public function test_it_can_create_a_warehouse(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->assertDispatched('open-modal', 'warehouse-form-modal')
            ->set('form.code', 'WH-DN')
            ->set('form.name', 'Da Nang Logistics Hub')
            ->set('form.address', 'Hai Chau District, Da Nang')
            ->set('form.is_active', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'warehouse-form-modal')
            ->assertSee('Da Nang Logistics Hub');

        $this->assertDatabaseHas('warehouses', [
            'code' => 'WH-DN',
            'name' => 'Da Nang Logistics Hub',
            'address' => 'Hai Chau District, Da Nang',
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
        Warehouse::create([
            'code' => 'WH-EXISTS',
            'name' => 'Existing Warehouse',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('create')
            ->set('form.code', 'WH-EXISTS')
            ->set('form.name', 'Another Warehouse')
            ->call('save')
            ->assertHasErrors(['form.code' => 'unique']);
    }

    public function test_it_can_load_warehouse_for_editing(): void
    {
        $warehouse = Warehouse::create([
            'code' => 'WH-EDIT',
            'name' => 'Original Facility',
            'address' => 'Old Address',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('edit', $warehouse->id)
            ->assertDispatched('open-modal', 'warehouse-form-modal')
            ->assertSet('form.code', 'WH-EDIT')
            ->assertSet('form.name', 'Original Facility')
            ->assertSet('form.address', 'Old Address')
            ->assertSet('isEditing', true);
    }

    public function test_it_can_update_a_warehouse_keeping_the_same_code(): void
    {
        $warehouse = Warehouse::create([
            'code' => 'WH-KEEP',
            'name' => 'Old Warehouse Name',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('edit', $warehouse->id)
            ->set('form.name', 'Updated Warehouse Name')
            ->set('form.address', 'Updated Address')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'warehouse-form-modal');

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'code' => 'WH-KEEP',
            'name' => 'Updated Warehouse Name',
            'address' => 'Updated Address',
        ]);
    }

    public function test_it_can_delete_a_warehouse(): void
    {
        $warehouse = Warehouse::create([
            'code' => 'WH-DEL',
            'name' => 'Warehouse to Delete',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('delete', $warehouse->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('warehouses', [
            'id' => $warehouse->id,
        ]);
    }
}
