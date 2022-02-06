<?php

namespace GloCurrency\AccessBank\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use GloCurrency\AccessBank\Models\DebitAccount;

class DebitAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DebitAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string,mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'account_number' => $this->faker->numerify('##########'),
            'name' => $this->faker->company(),
            'country_code' => $this->faker->unique()->countryISOAlpha3(),
            'currency_code' => $this->faker->unique()->currencyCode(),
        ];
    }
}
