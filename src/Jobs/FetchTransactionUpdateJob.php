<?php

namespace GloCurrency\AccessBank\Jobs;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Bus\Queueable;
use GloCurrency\MiddlewareBlocks\Enums\QueueTypeEnum as MQueueTypeEnum;
use GloCurrency\AccessBank\Models\Transaction;
use GloCurrency\AccessBank\Exceptions\SendTransactionException;
use GloCurrency\AccessBank\Exceptions\FetchTransactionUpdateException;
use GloCurrency\AccessBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\AccessBank\Enums\StatusCodeEnum;
use BrokeYourBike\AccessBank\Enums\ErrorCodeEnum;
use BrokeYourBike\AccessBank\Client;

class FetchTransactionUpdateJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    private Transaction $targetTransaction;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Transaction $targetTransaction)
    {
        $this->targetTransaction = $targetTransaction;
        $this->afterCommit();
        $this->onQueue(MQueueTypeEnum::SERVICES->value);
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->targetTransaction->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (TransactionStateCodeEnum::PROCESSING !== $this->targetTransaction->state_code) {
            throw FetchTransactionUpdateException::stateNotAllowed($this->targetTransaction);
        }

        try {
            /** @var Client */
            $api = App::makeWith(Client::class);


            if (in_array($this->targetTransaction->getBankCode(), ['044', '063'])) {
                $response = $api->fetchDomesticTransactionStatus((string) Str::uuid(), $this->targetTransaction->getReference());
            } else {
                $response = $api->fetchOtheBankTransactionStatus((string) Str::uuid(), $this->targetTransaction->getReference());
            }
        } catch (\Throwable $e) {
            report($e);
            throw FetchTransactionUpdateException::apiRequestException($e);
        }

        if ($response->errorCode === null || $response->transactionStatus === null) {
            return;
        }

        $errorCode = ErrorCodeEnum::tryFrom($response->errorCode);
        if (ErrorCodeEnum::NO_ERROR === $errorCode) {
            $statusCode = StatusCodeEnum::tryFrom($response->transactionStatus);
            if ($statusCode) {
                $this->targetTransaction->state_code = TransactionStateCodeEnum::makeFromStatusCode($statusCode);
                $this->targetTransaction->state_code_reason = $response->transactionInformation;
            }
            $this->targetTransaction->save();
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        report($exception);
    }
}
