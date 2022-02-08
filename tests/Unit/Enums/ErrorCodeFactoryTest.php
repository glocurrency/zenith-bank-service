<?php

namespace GloCurrency\ZenithBank\Tests\Unit\Enums;

use GloCurrency\ZenithBank\Tests\TestCase;
use GloCurrency\ZenithBank\Enums\TransactionStateCodeEnum;
use GloCurrency\ZenithBank\Enums\ErrorCodeFactory;
use BrokeYourBike\ZenithBank\Enums\ErrorCodeEnum;

class ErrorCodeFactoryTest extends TestCase
{
    /** @test */
    public function it_can_return_transaction_state_code_from_all_values()
    {
        foreach (ErrorCodeEnum::cases() as $value) {
            $this->assertInstanceOf(TransactionStateCodeEnum::class, ErrorCodeFactory::getTransactionStateCode($value));
        }
    }
}
