<?php

namespace GloCurrency\AccessBank\Enums;

use GloCurrency\MiddlewareBlocks\Enums\ProcessingItemStateCodeEnum as MProcessingItemStateCodeEnum;
use BrokeYourBike\AccessBank\Enums\StatusCodeEnum;
use BrokeYourBike\AccessBank\Enums\ErrorCodeEnum;

enum TransactionStateCodeEnum: string
{
    case LOCAL_UNPROCESSED = 'local_unprocessed';
    case LOCAL_EXCEPTION = 'local_exception';
    case STATE_NOT_ALLOWED = 'state_not_allowed';
    case API_REQUEST_EXCEPTION = 'api_request_exception';
    case RESULT_JSON_INVALID = 'result_json_invalid';
    case NO_ERROR_CODE_PROPERTY = 'no_error_code_property';
    case UNEXPECTED_ERROR_CODE = 'unexpected_error_code';
    case NO_STATUS_CODE_PROPERTY = 'no_transaction_status_property';
    case UNEXPECTED_STATUS_CODE = 'unexpected_transaction_status';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case FAILED = 'failed';
    case PROCESSING_ERROR = 'processing_error';
    case API_ERROR = 'api_error';
    case API_TIMEOUT = 'api_timeout';
    case DEBIT_ACCOUNT_INVALID = 'debit_account_invalid';
    case INSUFFICIENT_FUNDS = 'insufficient_funds';
    case DUPLICATE_TRANSACTION = 'duplicate_transaction';
    case TRANSACTION_AMOUNT_INVALID = 'transaction_amount_invalid';
    case SENDER_DETAILS_INVALID = 'sender_details_invalid';
    case SENDER_TRANSFER_LIMIT_EXCEEDED = 'sender_transfer_limit_exceeded';
    case RECIPIENT_BANK_ACCOUNT_INVALID = 'recipient_bank_account_invalid';
    case RECIPIENT_BANK_CODE_INVALID = 'recipient_bank_code_invalid';
    case RECIPIENT_NAME_VALIDATION_FAILED = 'recipient_name_validation_failed';
    case RECIPIENT_TRANSFER_LIMIT_EXCEEDED = 'recipient_transfer_limit_exceeded';
    case RECIPIENT_DETAILS_INVALID = 'recipient_details_invalid';

    public static function makeFromErrorCode(ErrorCodeEnum $errorCode): self
    {
        return match ($errorCode) {
            ErrorCodeEnum::NO_ERROR => self::PROCESSING,
            ErrorCodeEnum::UNAUTHORIZED => self::API_ERROR,
            ErrorCodeEnum::DUPLICATE_REQUEST => self::DUPLICATE_TRANSACTION,
            ErrorCodeEnum::NO_RECORD => self::API_ERROR,
            ErrorCodeEnum::INVALID_DEBIT_ACCOUNT => self::DEBIT_ACCOUNT_INVALID,
            ErrorCodeEnum::RECONFIRM_BENEFICIARY_ACCOUNT => self::RECIPIENT_BANK_ACCOUNT_INVALID,
            ErrorCodeEnum::UNABLE_TO_PROCESS_REQUEST => self::PROCESSING_ERROR,
            ErrorCodeEnum::BENEFICIARY_ACCOUNT_NO_PERMITTED => self::RECIPIENT_BANK_ACCOUNT_INVALID,
            ErrorCodeEnum::INSUFFICIENT_FUNDS => self::INSUFFICIENT_FUNDS,
            ErrorCodeEnum::INVALID_ACCOUNT_NUMBER => self::RECIPIENT_BANK_ACCOUNT_INVALID,
            ErrorCodeEnum::UNABLE_TO_PROCESS_ON_NIBSS => self::PROCESSING_ERROR,
            ErrorCodeEnum::UNABLE_TO_DEBIT => self::INSUFFICIENT_FUNDS,
            ErrorCodeEnum::INVALID_CREDIT_ACCOUNT => self::RECIPIENT_BANK_ACCOUNT_INVALID,
            ErrorCodeEnum::NOT_PERMITTED => self::API_ERROR,
            ErrorCodeEnum::ERROR_WHILE_PROCESSIN_TRANSACTION => self::PROCESSING_ERROR,
        };
    }

    public static function makeFromStatusCode(StatusCodeEnum $statusCode): self
    {
        return match ($statusCode) {
            StatusCodeEnum::PENDING => self::PROCESSING,
            StatusCodeEnum::SUCCESS => self::PAID,
            StatusCodeEnum::PROCESSING => self::PROCESSING,
            StatusCodeEnum::FAILED => self::FAILED,
            StatusCodeEnum::UNKNOWN => self::API_TIMEOUT,
        };
    }

    /**
     * Get the ProcessingItem state based on Transaction state.
     */
    public function getProcessingItemStateCode(): MProcessingItemStateCodeEnum
    {
        return match ($this) {
            self::LOCAL_UNPROCESSED => MProcessingItemStateCodeEnum::PENDING,
            self::LOCAL_EXCEPTION => MProcessingItemStateCodeEnum::MANUAL_RECONCILIATION_REQUIRED,
            self::STATE_NOT_ALLOWED => MProcessingItemStateCodeEnum::EXCEPTION,
            self::API_REQUEST_EXCEPTION => MProcessingItemStateCodeEnum::EXCEPTION,
            self::RESULT_JSON_INVALID => MProcessingItemStateCodeEnum::EXCEPTION,
            self::NO_ERROR_CODE_PROPERTY => MProcessingItemStateCodeEnum::EXCEPTION,
            self::UNEXPECTED_ERROR_CODE => MProcessingItemStateCodeEnum::EXCEPTION,
            self::NO_STATUS_CODE_PROPERTY => MProcessingItemStateCodeEnum::EXCEPTION,
            self::UNEXPECTED_STATUS_CODE => MProcessingItemStateCodeEnum::EXCEPTION,
            self::PROCESSING => MProcessingItemStateCodeEnum::PROVIDER_PENDING,
            self::PAID => MProcessingItemStateCodeEnum::PROCESSED,
            self::FAILED => MProcessingItemStateCodeEnum::MANUAL_RECONCILIATION_REQUIRED,
            self::PROCESSING_ERROR => MProcessingItemStateCodeEnum::MANUAL_RECONCILIATION_REQUIRED,
            self::API_ERROR => MProcessingItemStateCodeEnum::PROVIDER_NOT_ACCEPTING_TRANSACTIONS,
            self::API_TIMEOUT => MProcessingItemStateCodeEnum::PROVIDER_TIMEOUT,
            self::DEBIT_ACCOUNT_INVALID => MProcessingItemStateCodeEnum::PROVIDER_NOT_ACCEPTING_TRANSACTIONS,
            self::INSUFFICIENT_FUNDS => MProcessingItemStateCodeEnum::PROVIDER_NOT_ACCEPTING_TRANSACTIONS,
            self::DUPLICATE_TRANSACTION => MProcessingItemStateCodeEnum::EXCEPTION,
            self::TRANSACTION_AMOUNT_INVALID => MProcessingItemStateCodeEnum::TRANSACTION_AMOUNT_INVALID,
            self::SENDER_DETAILS_INVALID => MProcessingItemStateCodeEnum::SENDER_DETAILS_INVALID,
            self::SENDER_TRANSFER_LIMIT_EXCEEDED => MProcessingItemStateCodeEnum::RECIPIENT_BANK_ACCOUNT_INVALID,
            self::RECIPIENT_BANK_ACCOUNT_INVALID => MProcessingItemStateCodeEnum::RECIPIENT_BANK_ACCOUNT_INVALID,
            self::RECIPIENT_BANK_CODE_INVALID => MProcessingItemStateCodeEnum::RECIPIENT_BANK_CODE_INVALID,
            self::RECIPIENT_NAME_VALIDATION_FAILED => MProcessingItemStateCodeEnum::RECIPIENT_NAME_VALIDATION_FAILED,
            self::RECIPIENT_TRANSFER_LIMIT_EXCEEDED => MProcessingItemStateCodeEnum::RECIPIENT_TRANSFER_LIMIT_EXCEEDED,
            self::RECIPIENT_DETAILS_INVALID => MProcessingItemStateCodeEnum::RECIPIENT_DETAILS_INVALID,
        };
    }
}
