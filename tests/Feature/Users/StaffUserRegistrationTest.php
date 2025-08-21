<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StaffUserRegistrationTest extends TestCase
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
    }

    /**
     * Test that the users index screen can be rendered for a staff user.
     * The staff user has a 'marketing logistics associate' role.
     *
     * @return void
     */
    public function test_users_index_screen_can_be_rendered_for_staff()
    {
        // Create a staff user with the appropriate role.
        $staffUser = User::factory()->create();
        $staffUser->roles()->attach(Role::where('name', 'marketing logistics associate')->first());

        // Act as the staff user and visit the users index page.
        $response = $this->actingAs($staffUser)->get(route('users.index'));

        // Assert that the page is rendered successfully (HTTP 200 OK).
        $response->assertStatus(200);
    }

    /**
     * Test that the user creation screen can be rendered for a staff user.
     * The staff user has a 'marketing logistics associate' role.
     *
     * @return void
     */
    public function test_user_creation_screen_can_be_rendered_for_staff()
    {
        // Create a staff user with the appropriate role.
        $staffUser = User::factory()->create();
        $staffUser->roles()->attach(Role::where('name', 'marketing logistics associate')->first());

        // Act as the staff user and visit the user creation page.
        $response = $this->actingAs($staffUser)->get(route('users.create'));

        // Assert that the page is rendered successfully (HTTP 200 OK).
        $response->assertStatus(200);
    }

    /**
     * Test that an unauthorized user cannot access the user creation screen.
     * The user has a 'shipper' role, which is not allowed to create other users.
     *
     * @return void
     */
    public function test_unauthorized_user_cannot_access_user_creation_screen()
    {
        // Create an unauthorized user with a 'shipper' role.
        $unauthorizedUser = User::factory()->create();
        $unauthorizedUser->roles()->attach(Role::where('name', 'shipper')->first());

        // Act as the unauthorized user and visit the user creation page.
        $response = $this->actingAs($unauthorizedUser)->get(route('users.create'));

        // Assert that the page returns a 403 Forbidden error.
        $response->assertStatus(403);
    }

    /**
     * Test that a staff member can successfully create a new user (shipper).
     * This test simulates the staff-assisted registration process.
     *
     * @return void
     */
    public function test_authorised_staff_can_create_a_new_shipper_user()
    {
        // Create a staff user with a 'superadmin' role.
        $staffUser = User::factory()->create();
        $staffUser->roles()->attach(Role::where('name', 'superadmin')->first());

        // Define the data for the new user.
        $newUserData = User::factory()->make([
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->toArray();

        //prepare first name and surname variables
         $nameParts = explode(' ', trim($newUserData['contact_person']));
        $surname = array_pop($nameParts);

        // The remaining words are the first name(s)
        $first_name = implode(' ', $nameParts);

        // Use Livewire to test the component's full lifecycle.
        Livewire::actingAs($staffUser)
            ->test('auth.register')
            ->set('first_name', $first_name)
            ->set('surname', $surname)
            ->set('contact_phone', '263771234567') // Assuming a valid phone format
            ->set('phone_type', 'mobile')
            ->set('email', $newUserData['email'])
            ->set('customer_type', 'shipper')
            ->set('ownership_type', 'real_owner') // Or 'company'
            ->set('company_name', 'Acme Shipping Inc.')
            ->set('country', 'Zimbabwe')
            ->set('city', 'Harare')
            ->set('address', '123 Main Street')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register') // Call the 'register' method in the component
            ->assertRedirect(route('users.index')); // Assert redirect to the users index page

        // Assert that the new user exists in the database.
        $this->assertDatabaseHas('users', [
            'email' => $newUserData['email'],
        ]);
    }
    
    /**
     * Test that a staff member cannot create a user with an existing email.
     * This checks the unique email validation rule.
     *
     * @return void
     */
    public function test_staff_cannot_create_a_user_with_an_existing_email()
    {
        // Create an existing user in the database.
        $existingUser = User::factory()->create();

        // Create a staff user with a 'superadmin' role.
        $staffUser = User::factory()->create();
        $staffUser->roles()->attach(Role::where('name', 'superadmin')->first());

        // Attempt to create a new user with the same email as the existing one.
        Livewire::actingAs($staffUser)
            ->test('auth.register')
            ->set('first_name', 'New')
            ->set('surname', 'User')
            ->set('contact_phone', '263771234567')
            ->set('phone_type', 'mobile')
            ->set('email', $existingUser->email) // Use the existing email
            ->set('customer_type', 'shipper')
            ->set('ownership_type', 'individual')
            ->set('company_name', 'Acme Shipping Inc.')
            ->set('country', 'Zimbabwe')
            ->set('city', 'Harare')
            ->set('address', '123 Main Street')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasErrors(['email' => 'unique']); // Assert that a unique validation error occurred

        // Assert that no new user was created in the database.
        $this->assertDatabaseMissing('users', [
            'first_name' => 'New',
            'surname' => 'User',
        ]);
    }
}
