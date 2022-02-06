<?php

namespace GloCurrency\AccessBank\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GloCurrency\MiddlewareBlocks\Contracts\ModelWithStateCodeInterface as MModelWithStateCodeInterface;
use GloCurrency\AccessBank\Events\TransactionUpdatedEvent;
use GloCurrency\AccessBank\Events\TransactionCreatedEvent;
use GloCurrency\AccessBank\Enums\TransactionStateCodeEnum;
use GloCurrency\AccessBank\Database\Factories\TransactionFactory;
use GloCurrency\AccessBank\AccessBank;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\BaseModels\BaseUuid;
use BrokeYourBike\AccessBank\Interfaces\BankTransactionInterface;
use BrokeYourBike\AccessBank\Enums\StatusCodeEnum;
use BrokeYourBike\AccessBank\Enums\ErrorCodeEnum;

/**
 * GloCurrency\AccessBank\Models\Transaction
 *
 * @property string $id
 * @property string $transaction_id
 * @property string $processing_item_id
 * @property \GloCurrency\AccessBank\Enums\TransactionStateCodeEnum $state_code
 * @property string|null $state_code_reason
 * @property \BrokeYourBike\AccessBank\Enums\ErrorCodeEnum|null $error_code
 * @property string|null $error_code_description
 * @property \BrokeYourBike\AccessBank\Enums\StatusCodeEnum|null $status_code
 * @property string|null $status_code_description
 * @property string $reference
 * @property string $debit_account
 * @property string $recipient_account
 * @property string $recipient_name
 * @property string $bank_code
 * @property string $currency_code
 * @property float $amount
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Transaction extends BaseUuid implements MModelWithStateCodeInterface, SourceModelInterface, BankTransactionInterface
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'access_transactions';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<mixed>
     */
    protected $casts = [
        'state_code' => TransactionStateCodeEnum::class,
        'error_code' => ErrorCodeEnum::class,
        'status_code' => StatusCodeEnum::class,
        'amount' => 'double',
    ];

    /**
     * @var array<mixed>
     */
    protected $dispatchesEvents = [
        'created' => TransactionCreatedEvent::class,
        'updated' => TransactionUpdatedEvent::class,
    ];

    public function getStateCode(): TransactionStateCodeEnum
    {
        return $this->state_code;
    }

    public function getStateCodeReason(): ?string
    {
        return $this->state_code_reason;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrencyCode(): string
    {
        return $this->currency_code;
    }

    public function getDebitAccount(): string
    {
        return $this->debit_account;
    }

    public function getRecipientAccount(): string
    {
        return $this->recipient_account;
    }

    public function getRecipientName(): string
    {
        return $this->recipient_name;
    }

    public function getBankCode(): string
    {
        return $this->bank_code;
    }

    public function getDescription(): string
    {
        return $this->description ?? 'transaction';
    }

    /**
     * The ProcessingItem that Transaction belong to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function processingItem()
    {
        return $this->belongsTo(AccessBank::$processingItemModel, 'processing_item_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return TransactionFactory::new();
    }
}
