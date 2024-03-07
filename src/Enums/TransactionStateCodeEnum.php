<?php

namespace GloCurrency\ZenithBank\Enums;

use GloCurrency\MiddlewareBlocks\Enums\ProcessingItemStateCodeEnum as MProcessingItemStateCodeEnum;

enum TransactionStateCodeEnum: string
{
    case LOCAL_UNPROCESSED = 'local_unprocessed';
    case LOCAL_EXCEPTION = 'local_exception';
    case STATE_NOT_ALLOWED = 'state_not_allowed';
    case API_REQUEST_EXCEPTION = 'api_request_exception';
    case RESULT_JSON_INVALID = 'result_json_invalid';
    case UNEXPECTED_ERROR_CODE = 'unexpected_error_code';
    case PAID = 'paid';
    case UNKNOWN = 'unknown';
    case API_ERROR = 'api_error';
    case DUPLICATE_TRANSACTION = 'duplicate_transaction';
    case RECIPIENT_BANK_ACCOUNT_INVALID = 'recipient_bank_account_invalid';

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
            self::UNEXPECTED_ERROR_CODE => MProcessingItemStateCodeEnum::EXCEPTION,
            self::PAID => MProcessingItemStateCodeEnum::PROCESSED,
            self::UNKNOWN => MProcessingItemStateCodeEnum::MANUAL_RECONCILIATION_REQUIRED,
            self::API_ERROR => MProcessingItemStateCodeEnum::PROVIDER_NOT_ACCEPTING_TRANSACTIONS,
            self::DUPLICATE_TRANSACTION => MProcessingItemStateCodeEnum::EXCEPTION,
            self::RECIPIENT_BANK_ACCOUNT_INVALID => MProcessingItemStateCodeEnum::RECIPIENT_BANK_ACCOUNT_INVALID,
        };
    }
}
