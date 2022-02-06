<?php

namespace GloCurrency\AccessBank\Tests\Feature\Models;

use Illuminate\Support\Facades\Event;
use GloCurrency\AccessBank\Tests\FeatureTestCase;
use GloCurrency\AccessBank\Models\Transaction;
use GloCurrency\AccessBank\Events\TransactionCreatedEvent;

class CreateTransactionTest extends FeatureTestCase
{
    /** @test */
    public function fire_event_when_it_created(): void
    {
        Event::fake();

        Transaction::factory()->create();

        Event::assertDispatched(TransactionCreatedEvent::class);
    }

    /** @test */
    public function it_cannot_be_created_with_the_same_transaction_id()
    {
        Event::fake([
            TransactionCreatedEvent::class,
        ]);

        Transaction::factory()->create([
            'transaction_id' => 'trx-1',
        ]);

        try {
            Transaction::factory()->create([
                'transaction_id' => 'trx-1',
            ]);
        } catch (\Throwable $th) {
            $this->assertInstanceOf(\PDOException::class, $th);
            $this->assertCount(1, Transaction::where([
                'transaction_id' => 'trx-1',
            ])->get());
            return;
        }

        $this->fail('Exception was not thrown');
    }

    /** @test */
    public function it_cannot_be_created_with_the_same_reference()
    {
        Event::fake([
            TransactionCreatedEvent::class,
        ]);

        Transaction::factory()->create([
            'reference' => '1234',
        ]);

        try {
            Transaction::factory()->create([
                'reference' => '1234',
            ]);
        } catch (\Throwable $th) {
            $this->assertInstanceOf(\PDOException::class, $th);
            $this->assertCount(1, Transaction::where('reference', '1234')->get());
            return;
        }

        $this->fail('Exception was not thrown');
    }
}
