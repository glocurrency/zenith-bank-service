<?php

namespace GloCurrency\ZenithBank\Enums;

use GloCurrency\ZenithBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\ZenithBank\Enums\ErrorCodeEnum;

class ErrorCodeFactory
{
    public static function getTransactionStateCode(ErrorCodeEnum $errorCode): TransactionStateCodeEnum
    {
        return match ($errorCode) {
            ErrorCodeEnum::SUCCESS => TransactionStateCodeEnum::PAID,
            ErrorCodeEnum::DUPLICATE_TRANSACTION => TransactionStateCodeEnum::DUPLICATE_TRANSACTION,
            ErrorCodeEnum::UNABLE_TO_CREATE_REQUEST => TransactionStateCodeEnum::UNKNOWN,
            ErrorCodeEnum::UNKNOWN_STATE => TransactionStateCodeEnum::UNKNOWN,
            ErrorCodeEnum::TRANSACTION_QUERY_DATERANGE_EXPIRED => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::TRANSACTION_NOT_FOUND => TransactionStateCodeEnum::UNKNOWN,
            ErrorCodeEnum::AUTH_TOKEN_REQUIRED => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::EXPIRED_TOKEN => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::INVALID_TOKEN => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::INVALID_USER_CREDENTIALS => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::INACTIVE_CREDENTIALS => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::ERROR_PROCESSING_REQUEST => TransactionStateCodeEnum::UNKNOWN,
            ErrorCodeEnum::INVALID_CALLER_IP_ADDRESS => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::WRONG_ACCOUNT_PASSED => TransactionStateCodeEnum::RECIPIENT_BANK_ACCOUNT_INVALID,
            ErrorCodeEnum::SYSTEM_EXCEPTION => TransactionStateCodeEnum::UNKNOWN,
            ErrorCodeEnum::INTERNAL_BAD_REQUEST => TransactionStateCodeEnum::UNKNOWN,
        };
    }
}
