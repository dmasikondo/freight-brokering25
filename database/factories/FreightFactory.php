<?php

namespace Database\Factories;

use App\Models\Freight;
use App\Models\User;
use App\Enums\FreightStatus;
use App\Enums\ShipmentStatus;
use App\Enums\PricingType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FreightFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Freight::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    
    public function definition(): array
    {
        $dateFrom = $this->faker->dateTimeBetween('now', '+1 week');
        $dateTo = $this->faker->dateTimeBetween($dateFrom, '+2 weeks');
        
        // Default to DRAFT or SUBMITTED
        $status = $this->faker->randomElement([FreightStatus::DRAFT, FreightStatus::SUBMITTED]);
        $isPublished = ($status === FreightStatus::SUBMITTED);
        
        // Determine payment option and budget based on option
        $paymentOption = $this->faker->randomElement(PricingType::cases());
        $budget = ($paymentOption === PricingType::FullBudget) ? $this->faker->randomFloat(2, 1000, 10000) : null;
        $carriageRate = ($paymentOption === PricingType::RateOfCarriage) ? $this->faker->randomFloat(2, 0.5, 2.5) . '/km' : null;
        $user  = User::whereEmail('dmasikondo@gmail.com')->firstOrFail();
        
        return [
            // Foreign Keys
            'creator_id' => $user->id,
            'publisher_id' => null, // Default to null, can be set in a state
            
            // Basic Details
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(2),
            'weight' => $this->faker->randomFloat(2, 500, 5000) . ' kg',
            
            // Locations
            'cityfrom' => $this->faker->city(),
            'cityto' => $this->faker->city(),
            'countryfrom' => $this->faker->country(),
            'countryto' => $this->faker->country(),
            'pickup_address' => $this->faker->streetAddress(),
            'delivery_address' => $this->faker->streetAddress(),

            // Dates
            'datefrom' => $dateFrom,
            'dateto' => $dateTo,
            
            // Status and Enums
            'is_published' => $isPublished,
            'status' => $status->value,
            'shipment_status' => ShipmentStatus::INAPPLICABLE->value,
            'payment_option' => $paymentOption->value,
            
            // Pricing/Vehicle
            'budget' => $budget,
            'carriage_rate' => $carriageRate,
            'vehicle_type' => $this->faker->randomElement(['Flatbed', 'Curtainside', 'Refrigerated']),
            'distance' => $this->faker->numberBetween(100, 2000) . ' km',
            
            // Flags
            'is_read' => $this->faker->boolean(20), // 20% chance of being read
            'is_hazardous' => $this->faker->boolean(10), // 10% chance of being hazardous

            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    /**
     * Indicate that the freight is published (and therefore submitted/published status).
     */
    
    public function published(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'status' => FreightStatus::PUBLISHED->value,
            'publisher_id' => '66', // Assign a publisher when published
            'shipment_status' => ShipmentStatus::LOADING->value,
        ]);
    }

    /**
     * Indicate that the freight is a draft.
     */
    public function draft(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'status' => FreightStatus::DRAFT->value,
            'publisher_id' => null,
            'shipment_status' => ShipmentStatus::INAPPLICABLE->value,
        ]);
    }

    /**
     * Indicate that the freight is expired.
     */
    public function expired(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => FreightStatus::EXPIRED->value,
            'dateto' => Carbon::yesterday(),
        ]);
    }
}