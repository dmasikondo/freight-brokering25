<?php

namespace Tests\Feature\Livewire\Users;

use App\Models\Role;
use App\Models\User;
use App\Models\UserCreation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SearchUserTest extends TestCase
{
    // Use RefreshDatabase to reset the database after each test.
    use RefreshDatabase;

    /**
     * Set up the test environment.
     * This method is run before each test method.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create the roles required for the tests.
        Role::firstOrCreate(['name' => 'superadmin']);
        Role::firstOrCreate(['name' => 'shipper']);
        Role::firstOrCreate(['name' => 'marketing logistics associate']);

        // Authenticate as a user with sufficient permissions for viewAny policy.
        // The policy is typically tied to a role like 'superadmin'.
        $staffUser = User::factory()->create();
        $staffUser->roles()->attach(Role::where('name', 'superadmin')->first());
        $this->actingAs($staffUser);
    }

    /**
     * Test that the component renders and shows all users when no search term is provided.
     *
     * @return void
     */
    public function test_component_shows_all_users_when_search_is_empty()
    {
        // Create multiple users to ensure they are all displayed.
        $users = User::factory()->count(5)->create();

        Livewire::test('users.user-index')
            ->assertSet('search', '')
            ->assertSeeHtml($users->first()->email)
            ->assertSeeHtml($users->last()->email);
    }

    /**
     * Test that the search functionality correctly filters users by their own fields.
     *
     * @return void
     */
    public function test_search_filters_by_user_fields()
    {
        // Create a user that will match the search term.
        $matchingUser = User::factory()->create([
            'contact_person' => 'JohnDoe'
        ]);
        
        // Create a user that should not be found.
        $nonMatchingUser = User::factory()->create([
            'contact_person' => 'JaneDoe'
        ]);

        Livewire::test('users.user-index')
            ->set('search', 'JohnDoe')
            ->assertSeeHtml($matchingUser->email)
            ->assertDontSeeHtml($nonMatchingUser->email);
    }

    /**
     * Test that the search functionality correctly filters users by their creator (createdBy).
     *
     * @return void
     */
    public function test_search_filters_by_created_by_user()
    {
        // Create a user who will act as the creator.
        $creator = User::factory()->create([
            'contact_person' => 'Creator'
        ]);
        
        
        
        // Create a user that was created by the 'Creator'.
        $createdUser = User::factory()->create([
            'contact_person' => 'CreatedUser'
        ]);
        
        // Establish the relationship using the pivot table.
        UserCreation::create([
            'creator_user_id' => $creator->id,
            'created_user_id' => $createdUser->id
        ]);
        
        // Search for the creator's name.
        Livewire::test('users.user-index')
            ->set('search', 'Creator')
            ->assertSeeHtml($createdUser->email)
            ->assertSeeHtml($createdUser->createdBy->contact_person); 
    }

    /**
     * Test that the search functionality correctly filters users by their role.
     *
     * @return void
     */
    public function test_search_filters_by_role_name()
    {
        // Create a shipper user.
        $shipper = User::factory()->create();
        $shipper->roles()->attach(Role::where('name', 'shipper')->first());

        // Create a user with a different role.
        $marketingUser = User::factory()->create();
        $marketingUser->roles()->attach(Role::where('name', 'marketing logistics associate')->first());

        // Establish the relationship using the pivot table.
        UserCreation::create([
            'creator_user_id' => $marketingUser->id,
            'created_user_id' => $shipper->id
        ]);        

        // Search for the 'shipper' role name.
        Livewire::test('users.user-index')
            ->set('search', 'shipper')
            ->assertSeeHtml($shipper->email)
            ->assertSeeHtml($marketingUser->contact_person);
    }

    /**
     * Test that a search term matching multiple criteria returns all relevant users.
     *
     * @return void
     */
    public function test_combined_search_works_correctly()
    {
        // User 1: matches by email.
        $userByEmail = User::factory()->create([
            'email' => 'search@example.com'
        ]);

        // User 2: matches by their creator's name.
        $creator = User::factory()->create([
            'contact_person' => 'SearchName'
        ]);
        $userByCreator = User::factory()->create();
        UserCreation::create([
            'creator_user_id' => $creator->id,
            'created_user_id' => $userByCreator->id
        ]);

        // User 3: matches by role.
        $userByRole = User::factory()->create();
        $userByRole->roles()->attach(Role::where('name', 'marketing logistics associate')->first());

        // Create a user that won't match any criteria.
        $nonMatchingUser = User::factory()->create();

        // Perform a single search that should find all three users.
        Livewire::test('users.user-index')
            ->set('search', 'search')
            ->assertSeeHtml($userByEmail->email)
            ->assertSeeHtml($userByCreator->contact_person)
            ->assertSeeHtml($userByRole->name)
            ->assertDontSeeHtml($nonMatchingUser->email);
    }
}
