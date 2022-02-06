<?php

namespace GloCurrency\AccessBank\Tests\Unit\Models;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\SoftDeletes;
use GloCurrency\AccessBank\Tests\TestCase;
use GloCurrency\AccessBank\Models\Transaction;
use GloCurrency\AccessBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\BaseModels\BaseUuid;
use BrokeYourBike\AccessBank\Enums\StatusCodeEnum;
use BrokeYourBike\AccessBank\Enums\ErrorCodeEnum;

class TransactionTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_extends_base_model(): void
    {
        $parent = get_parent_class(Transaction::class);

        $this->assertSame(BaseUuid::class, $parent);
    }

    /** @test */
    public function it_uses_soft_deletes(): void
    {
        $usedTraits = class_uses(Transaction::class);

        $this->assertArrayHasKey(SoftDeletes::class, $usedTraits);
    }

    /** @test */
    public function it_implemets_source_model_interface(): void
    {
        $this->assertInstanceOf(SourceModelInterface::class, new Transaction());
    }

    /** @test */
    public function it_returns_amount_as_float(): void
    {
        $transaction = new Transaction();
        $transaction->amount = '1.02';

        $this->assertSame(1.02, $transaction->amount);
    }

    /** @test */
    public function it_returns_state_code_as_enum(): void
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED->value,
        ]);

        $this->assertEquals(TransactionStateCodeEnum::LOCAL_UNPROCESSED, $transaction->state_code);
    }

    /** @test */
    public function it_returns_error_code_as_enum(): void
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'error_code' => ErrorCodeEnum::NO_ERROR->value,
        ]);

        $this->assertEquals(ErrorCodeEnum::NO_ERROR, $transaction->error_code);
    }

    /** @test */
    public function it_returns_status_code_as_enum(): void
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'status_code' => StatusCodeEnum::SUCCESS->value,
        ]);

        $this->assertEquals(StatusCodeEnum::SUCCESS, $transaction->status_code);
    }
}
