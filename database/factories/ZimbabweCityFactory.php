<?php

namespace Database\Factories;

use App\Models\Province;
use App\Models\ZimbabweCity;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZimbabweCityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ZimbabweCity::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->city(),
            'abbreviation' => $this->faker->unique()->lexify('???'),
            'province_id' => Province::factory(),
        ];
    }
}
