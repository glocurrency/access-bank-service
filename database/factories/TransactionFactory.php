<?php

namespace GloCurrency\AccessBank\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use GloCurrency\AccessBank\Models\Transaction;
use GloCurrency\AccessBank\Enums\TransactionStateCodeEnum;
use GloCurrency\AccessBank\AccessBank;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string,mixed>
     */
    public function definition()
    {
        $transactionModel = AccessBank::$transactionModel;
        $processingItemModel = AccessBank::$processingItemModel;

        return [
            'id' => $this->faker->uuid(),
            'transaction_id' => $transactionModel::factory(),
            'processing_item_id' => $processingItemModel::factory(),
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
            'reference' => $this->faker->uuid(),
            'debit_account' => $this->faker->numerify('##########'),
            'recipient_account' => $this->faker->numerify('##########'),
            'recipient_name' => $this->faker->name(),
            'bank_code' => $this->faker->unique()->word(),
            'amount' => $this->faker->randomFloat(2, 1),
            'currency_code' => $this->faker->currencyCode(),
            'description' => $this->faker->sentence(2),
        ];
    }
}
