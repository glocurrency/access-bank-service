<?php

namespace GloCurrency\AccessBank\Jobs;

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
use GloCurrency\AccessBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\AccessBank\Enums\StatusCodeEnum;
use BrokeYourBike\AccessBank\Enums\ErrorCodeEnum;
use BrokeYourBike\AccessBank\Client;

class SendTransactionJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
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
        if (TransactionStateCodeEnum::LOCAL_UNPROCESSED !== $this->targetTransaction->getStateCode()) {
            throw SendTransactionException::stateNotAllowed($this->targetTransaction);
        }

        try {
            /** @var Client */
            $api = App::make(Client::class);
            $response = $api->sendDomesticTransaction($this->targetTransaction);
        } catch (\Throwable $e) {
            report($e);
            throw SendTransactionException::apiRequestException($e);
        }

        if ($response->errorCode === null) {
            throw SendTransactionException::noErrorCode($response);
        }

        $errorCode = ErrorCodeEnum::tryFrom($response->errorCode);

        if (!$errorCode) {
            throw SendTransactionException::unexpectedErrorCode($response->errorCode);
        }

        $this->targetTransaction->error_code = $errorCode;
        $this->targetTransaction->state_code = TransactionStateCodeEnum::makeFromErrorCode($errorCode);

        if (ErrorCodeEnum::NO_ERROR === $errorCode) {
            if ($response->transactionStatus === null) {
                throw SendTransactionException::noStatusCode($response);
            }

            $statusCode = StatusCodeEnum::tryFrom($response->transactionStatus);

            if (!$statusCode) {
                throw SendTransactionException::unexpectedStatusCode($response->transactionStatus);
            }

            $this->targetTransaction->status_code = $statusCode;
            $this->targetTransaction->status_code_description = $response->transactionInformation;
            $this->targetTransaction->state_code = TransactionStateCodeEnum::makeFromStatusCode($statusCode);
        }

        $this->targetTransaction->save();
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

        if ($exception instanceof SendTransactionException) {
            $this->targetTransaction->update([
                'state_code' => $exception->getStateCode(),
                'state_code_reason' => $exception->getStateCodeReason(),
            ]);
            return;
        }

        $this->targetTransaction->update([
            'state_code' => TransactionStateCodeEnum::LOCAL_EXCEPTION,
            'state_code_reason' => $exception->getMessage(),
        ]);
    }
}
