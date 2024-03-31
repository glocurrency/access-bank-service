<?php

namespace GloCurrency\AccessBank\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use GloCurrency\AccessBank\Models\Bank;
use GloCurrency\AccessBank\AccessBank;

class BankFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Bank::class;

    /**
     * Define the model's default state.
     *
     * @return array<string,mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'bank_id' => (AccessBank::$bankModel)::factory(),
            'codename' => $this->faker->word(),
            'domestic' => false,
        ];
    }
}
