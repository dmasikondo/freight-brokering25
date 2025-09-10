<?php

namespace Tests\Feature\Livewire\Territories;

use App\Models\Territory;
use App\Models\User;
use Livewire\Volt\Volt;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TerritoryListingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and authenticate them for the tests.
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_territory_index_page_can_be_rendered(): void
    {
        // Check that the Volt component can be rendered without errors.
        Volt::test('territory.index')
            ->assertStatus(200);
    }

    public function test_it_displays_territories_ordered_by_name(): void
    {
        // Create territories out of order to test the sorting.
        $territoryB = Territory::factory()->create(['name' => 'B Territory']);
        $territoryA = Territory::factory()->create(['name' => 'A Territory']);
        $territoryC = Territory::factory()->create(['name' => 'C Territory']);

        Volt::test('territory.index')
            ->assertSeeInOrder([
                $territoryA->name,
                $territoryB->name,
                $territoryC->name,
            ]);
    }

    public function test_a_territory_can_be_deleted(): void
    {
        // Create a territory to be deleted.
        $territory = Territory::factory()->create();
        $territoryInserted = Territory::findOrFail($territory->id);

        // Assert that a territory exists before deletion.
        $this->assertDatabaseCount('territories', 1);

        // Test that the deleteTerritory method works as expected.
        Volt::test('territory.index')
            ->call('deleteTerritory', $territory->id)
            ->assertSeeText('Territory successfully deleted.');

        // Assert that the territory has been soft-deleted.
        $this->assertSoftDeleted('territories', ['id' => $territory->id]);     
        $this->assertDatabaseCount('territories', 1);
    }

    public function test_deleting_non_existent_territory_shows_error_message(): void
    {
        // Attempt to delete a territory that does not exist.
        $nonExistentId = 999;

        Volt::test('territory.index')
            ->call('deleteTerritory', $nonExistentId)
            // Assert that the session has a flash message with 'error'.
            ->assertSee('Territory could not be deleted.');
    }
}
