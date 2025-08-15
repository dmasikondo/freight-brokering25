<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_carriers_can_self_register(): void
    {
        $this->assertUserCanRegister('carrier');
    }

    public function test_new_shippers_can_self_register(): void
    {
        $this->assertUserCanRegister('shipper');
    }

    public function test_registered_email_cannot_reregister(): void
    {
        // First, register a user
        $this->assertUserCanRegister('shipper');

        // Attempt to register again with the same email
        $response = Volt::test('auth.register')
            ->set('first_name', 'Test')
            ->set('surname', 'User')
            ->set('phone_type', 'mobile')
            ->set('contact_phone', '0772421868')
            ->set('customer_type', 'carrier')
            ->set('ownership_type', 'real_owner')
            ->set('company_name', 'test company')
            ->set('country', 'Zimbabwe')
            ->set('city', 'harare')
            ->set('address', '12345')
            ->set('whatsapp', '8523698555')
            ->set('email', 'test@example.com') // Same email as before
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        // Assert that there are validation errors for the email
        $response->assertHasErrors(['email']);
    }    

    private function assertUserCanRegister(string $customerType): void
    {
        $response = Volt::test('auth.register')
            ->set('first_name', 'Test')
            ->set('surname', 'User')
            ->set('phone_type', 'mobile')
            ->set('contact_phone', '0772421868')
            ->set('customer_type', $customerType)
            ->set('ownership_type', 'real_owner')
            ->set('company_name', 'test company')
            ->set('country', 'Zimbabwe')
            ->set('city', 'harare')
            ->set('address', '12345')
            ->set('whatsapp', '8523698555')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');           

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }
}