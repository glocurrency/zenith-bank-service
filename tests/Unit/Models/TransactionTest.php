<?php

namespace GloCurrency\ZenithBank\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\SoftDeletes;
use GloCurrency\ZenithBank\Models\Transaction;
use GloCurrency\ZenithBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\ZenithBank\Enums\PostedStatusEnum;
use BrokeYourBike\ZenithBank\Enums\ErrorCodeEnum;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\BaseModels\BaseUuid;

class TransactionTest extends TestCase
{
    /** @test */
    public function it_extends_base_model(): void
    {
        $parent = get_parent_class(Transaction::class);

        $this->assertSame(BaseUuid::class, $parent);
    }

    /** @test */
    public function it_uses_soft_deletes(): void
    {
        $usedTraits = class_uses(Transaction::class);

        $this->assertArrayHasKey(SoftDeletes::class, $usedTraits);
    }

    /** @test */
    public function it_implemets_source_model_interface(): void
    {
        $this->assertInstanceOf(SourceModelInterface::class, new Transaction());
    }

    /** @test */
    public function it_returns_amount_as_boolean(): void
    {
        $transaction = new Transaction();
        $transaction->amount = '1.02';

        $this->assertSame(1.02, $transaction->amount);
    }

    /** @test */
    public function it_returns_should_resend_as_boolean(): void
    {
        $transaction = new Transaction();
        $transaction->should_resend = '0';

        $this->assertSame(false, $transaction->should_resend);
    }

    /** @test */
    public function it_returns_state_as_enum(): void
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'state_code' => TransactionStateCodeEnum::PAID->value,
        ]);

        $this->assertEquals(TransactionStateCodeEnum::PAID, $transaction->state_code);
    }

    /** @test */
    public function it_returns_error_code_as_enum(): void
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'error_code' => ErrorCodeEnum::SUCCESS->value,
        ]);

        $this->assertEquals(ErrorCodeEnum::SUCCESS, $transaction->error_code);
    }

    /** @test */
    public function it_returns_posted_status_as_enum(): void
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'posted_status' => PostedStatusEnum::YES->value,
        ]);

        $this->assertEquals(PostedStatusEnum::YES, $transaction->posted_status);
    }
}
