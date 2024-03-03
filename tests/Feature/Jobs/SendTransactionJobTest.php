<?php

namespace GloCurrency\AccessBank\Tests\Feature\Jobs;

use Illuminate\Support\Facades\Event;
use GloCurrency\AccessBank\Tests\FeatureTestCase;
use GloCurrency\AccessBank\Models\Transaction;
use GloCurrency\AccessBank\Jobs\SendTransactionJob;
use GloCurrency\AccessBank\Exceptions\SendTransactionException;
use GloCurrency\AccessBank\Events\TransactionUpdatedEvent;
use GloCurrency\AccessBank\Events\TransactionCreatedEvent;
use GloCurrency\AccessBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\AccessBank\Enums\StatusCodeEnum;
use BrokeYourBike\AccessBank\Enums\ErrorCodeEnum;
use BrokeYourBike\AccessBank\Client;

class SendTransactionJobTest extends FeatureTestCase
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
    public function it_will_throw_if_response_code_is_unexpected(): void
    {
        /** @var Transaction */
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
        ]);

        [$httpMock] = $this->mockApiFor(Client::class);
        $httpMock->append($this->makeAuthResponse());
        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{
            "success": true,
            "message": "",
            "errorCode": "' . ErrorCodeEnum::NO_ERROR->value . '",
            "payment": {
                "status": "not a code you can expect"
            }
        }'));

        try {
            SendTransactionJob::dispatchSync($targetTransaction);
        } catch (\Throwable $th) {
            $this->assertEquals('Unexpected ' . StatusCodeEnum::class . ': `not a code you can expect`', $th->getMessage());
            $this->assertInstanceOf(SendTransactionException::class, $th);
        }

        /** @var Transaction */
        $targetTransaction = $targetTransaction->fresh();

        $this->assertEquals(TransactionStateCodeEnum::PROCESSING, $targetTransaction->state_code);
    }

    /** @test */
    public function it_can_send_transaction(): void
    {
        /** @var Transaction */
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
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
            "message": "",
            "success": true
        }'));

        SendTransactionJob::dispatchSync($targetTransaction);

        /** @var Transaction */
        $targetTransaction = $targetTransaction->fresh();

        $this->assertEquals(StatusCodeEnum::SUCCESS, $targetTransaction->status_code);
        $this->assertEquals('Transaction completed successfully', $targetTransaction->status_code_description);
        $this->assertEquals(ErrorCodeEnum::NO_ERROR, $targetTransaction->error_code);
        $this->assertEquals(TransactionStateCodeEnum::PAID, $targetTransaction->state_code);
    }
}
