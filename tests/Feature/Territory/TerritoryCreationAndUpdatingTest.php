<?php

namespace Tests\Feature\Livewire\Territories;

use App\Models\Territory;
use App\Models\User;
use App\Models\Country;
use App\Models\Province;
use App\Models\ZimbabweCity;
use Livewire\Volt\Volt;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TerritoryCreationAndUpdatingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_create_territory_page_can_be_rendered(): void
    {
        Volt::test('territory.create')
            ->assertStatus(200)
            ->assertSeeText('Create Territory');
    }

    public function test_territory_can_be_created_with_relationships(): void
    {
        // Set up the necessary data for a create operation. Note the lowercase 'zimbabwe'
        // to match the component's logic.
        $country = Country::factory()->create(['name' => 'zimbabwe']);
        $province = Province::factory()->create(['name' => 'Harare']);
        $city = ZimbabweCity::factory()->create(['province_id' => $province->id, 'name' => 'Harare']);

        $this->assertDatabaseCount('territories', 0);
        
        Volt::test('territory.create')
            ->set('territory', 'Test Territory')
            ->set('selectedCountry', [$country->name])
            ->set('selectedCities', [$city->id])
            ->call('createTerritory')
            ->assertRedirectToRoute('territories.index')
            ->assertSessionHas('message', 'Territory successfully created.');

        // Assert that the territory and its relationships were created
        $this->assertDatabaseCount('territories', 1);
        $territory = Territory::first();
        $this->assertEquals('Test Territory', $territory->name);
        $this->assertCount(1, $territory->countries);
        $this->assertCount(1, $territory->provinces);
        $this->assertCount(0, $territory->zimbabweCities);
    }

    public function test_territory_creation_requires_name_and_country(): void
    {
        // Test validation for missing territory name
        Volt::test('territory.create')
            ->set('territory', '')
            ->set('selectedCountry', ['Zimbabwe'])
            ->call('createTerritory')
            ->assertHasErrors(['territory' => 'required']);

        // Test validation for missing country selection
        Volt::test('territory.create')
            ->set('territory', 'Test Territory')
            ->set('selectedCountry', [])
            ->call('createTerritory')
            ->assertHasErrors(['selectedCountry' => 'required']);
    }

    public function test_existing_territory_data_is_correctly_mounted(): void
    {
        // Create a territory with associated relationships.
        $country = Country::factory()->create(['name' => 'Zimbabwe']);
        $province = Province::factory()->create(['name' => 'Harare']);
        $city = ZimbabweCity::factory()->create(['province_id' => $province->id, 'name' => 'Harare']);
        $territory = Territory::factory()->create(['name' => 'Old Territory Name']);
        $territory->countries()->sync([$country->id]);
        $territory->provinces()->sync([$province->id]);
        $territory->zimbabweCities()->sync([$city->id]);

        $component = Volt::test('territory.create', ['territory' => $territory->id]);
        
        // Assert that the component's properties were correctly populated by the mount method.
        $this->assertEquals($territory->name, $component->get('territory'));
        $this->assertContains($country->name, $component->get('selectedCountry'));
        $this->assertContains($province->id, $component->get('selectedProvinces'));
        $this->assertContains($city->id, $component->get('selectedCities'));
    }

    public function test_existing_territory_can_be_updated_with_relationships(): void
    {
        // Create initial territory and relationships. Note the lowercase 'zimbabwe'
        // to match the component's logic.
        $oldTerritory = Territory::factory()->create(['name' => 'Old Territory Name']);
        $country = Country::factory()->create(['name' => 'zimbabwe']);
        $oldTerritory->countries()->attach($country->id);

        // Create new relationships for the update
        $newCountry = Country::factory()->create(['name' => 'South Africa']);
        $newProvince = Province::factory()->create(['name' => 'Gauteng']);
        $newCity = ZimbabweCity::factory()->create(['province_id' => $newProvince->id, 'name' => 'Johannesburg']);

        Volt::test('territory.create', ['territory' => $oldTerritory->id])
            ->set('territory', 'New Territory Name')
            ->set('selectedCountry', [$newCountry->name])
            ->set('selectedCities', [$newCity->id])
            ->call('createTerritory')
            ->assertRedirectToRoute('territories.index')
            ->assertSessionHas('message', 'Territory successfully updated.');

        // Assert the territory was updated.
        $this->assertDatabaseHas('territories', ['name' => 'New Territory Name']);
        $this->assertDatabaseMissing('territories', ['name' => 'Old Territory Name']);

        // Assert that the new relationships were synced and old ones were detached
        $updatedTerritory = Territory::find($oldTerritory->id);
        $this->assertCount(1, $updatedTerritory->countries);
        $this->assertCount(1, $updatedTerritory->provinces);
        $this->assertCount(0, $updatedTerritory->zimbabweCities);
        $this->assertEquals('South Africa', $updatedTerritory->countries->first()->name);
    }
}
