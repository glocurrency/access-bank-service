<?php

namespace GloCurrency\AccessBank\Tests\Feature\Jobs;

use Illuminate\Support\Facades\Event;
use GloCurrency\AccessBank\Tests\FeatureTestCase;
use GloCurrency\AccessBank\Models\Transaction;
use GloCurrency\AccessBank\Jobs\FetchTransactionUpdateJob;
use GloCurrency\AccessBank\Events\TransactionUpdatedEvent;
use GloCurrency\AccessBank\Events\TransactionCreatedEvent;
use GloCurrency\AccessBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\AccessBank\Enums\StatusCodeEnum;
use BrokeYourBike\AccessBank\Enums\ErrorCodeEnum;
use BrokeYourBike\AccessBank\Client;

class FetchTransactionUpdateJobTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TransactionCreatedEvent::class,
            TransactionUpdatedEvent::class,
        ]);
    }

    private function makeAuthResponse(): \GuzzleHttp\Psr7\Response
    {
        return new \GuzzleHttp\Psr7\Response(200, [], '{
            "expires_in": "3599",
            "access_token": "super-secure-token"
        }');
    }

    /** @test */
    public function it_can_update_transaction(): void
    {
        /** @var Transaction */
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::PROCESSING,
        ]);

        [$httpMock] = $this->mockApiFor(Client::class);
        $httpMock->append($this->makeAuthResponse());
        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{
            "payment": {
                "transactionId": "BANKAPI132465789",
                "status": ' . StatusCodeEnum::SUCCESS->value . ',
                "information": "Transaction completed successfully"
            },
            "errorCode": ' . ErrorCodeEnum::NO_ERROR->value . ',
            "message": null,
            "success": true
        }'));

        FetchTransactionUpdateJob::dispatchSync($targetTransaction);

        /** @var Transaction */
        $targetTransaction = $targetTransaction->fresh();

        $this->assertEquals(TransactionStateCodeEnum::PAID, $targetTransaction->state_code);
        $this->assertEquals('Transaction completed successfully', $targetTransaction->state_code_reason);
    }
}
