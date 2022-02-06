<?php

namespace GloCurrency\AccessBank\Tests\Feature\Models;

use Illuminate\Support\Facades\Event;
use GloCurrency\AccessBank\Tests\FeatureTestCase;
use GloCurrency\AccessBank\Models\Transaction;
use GloCurrency\AccessBank\Events\TransactionUpdatedEvent;
use GloCurrency\AccessBank\Events\TransactionCreatedEvent;
use GloCurrency\AccessBank\Enums\TransactionStateCodeEnum;

class UpdateTransactionTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TransactionCreatedEvent::class,
        ]);
    }

    /** @test */
    public function fire_event_when_it_updated(): void
    {
        $transaction = Transaction::factory()->create([
            'state_code_reason' => 'abc',
        ]);

        Event::fake();

        $transaction->state_code_reason = 'xyz';
        $transaction->save();

        Event::assertDispatched(TransactionUpdatedEvent::class);
    }
}
