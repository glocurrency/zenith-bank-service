<?php

namespace GloCurrency\ZenithBank\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use GloCurrency\ZenithBank\ZenithBank;
use GloCurrency\ZenithBank\Models\Transaction;
use GloCurrency\ZenithBank\Enums\TransactionStateCodeEnum;

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
        return [
            'id' => $this->faker->uuid(),
            'transaction_id' => (ZenithBank::$transactionModel)::factory(),
            'processing_item_id' => (ZenithBank::$processingItemModel)::factory(),
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
            'reference' => $this->faker->uuid(),
            'debit_account' => $this->faker->numerify('##########'),
            'sender_name' => $this->faker->name(),
            'recipient_account' => $this->faker->numerify('##########'),
            'recipient_name' => $this->faker->name(),
            'amount' => $this->faker->randomFloat(2, 1),
            'should_resend' => false,
        ];
    }
}
