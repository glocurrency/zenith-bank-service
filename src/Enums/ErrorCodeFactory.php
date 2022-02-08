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
            ErrorCodeEnum::WRONG_REQUEST => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::UNAUTHENTICATED => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::ERROR => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::INVALID_ACCOUNT => TransactionStateCodeEnum::RECIPIENT_BANK_ACCOUNT_INVALID,
            ErrorCodeEnum::SYSTEM_EXCEPTION => TransactionStateCodeEnum::API_ERROR,
        };
    }
}
