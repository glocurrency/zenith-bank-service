<?php

namespace GloCurrency\ZenithBank\Tests\Unit\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use GloCurrency\ZenithBank\Tests\TestCase;
use GloCurrency\ZenithBank\Models\Transaction;
use GloCurrency\ZenithBank\Jobs\SendTransactionJob;
use GloCurrency\MiddlewareBlocks\Enums\QueueTypeEnum as MQueueTypeEnum;

class SendTransactionJobTest extends TestCase
{
    /** @test */
    public function it_has_tries_defined(): void
    {
        /** @var Transaction */
        $transaction = new Transaction();

        $job = (new SendTransactionJob($transaction));
        $this->assertSame(1, $job->tries);
    }

    /** @test */
    public function it_has_dispatch_queue_specified()
    {
        /** @var Transaction */
        $transaction = new Transaction();

        $job = (new SendTransactionJob($transaction));
        $this->assertEquals(MQueueTypeEnum::SERVICES->value, $job->queue);
    }

    /** @test */
    public function it_implements_should_be_unique(): void
    {
        /** @var Transaction */
        $transaction = new Transaction();

        $job = (new SendTransactionJob($transaction));
        $this->assertInstanceOf(ShouldBeUnique::class, $job);
        $this->assertSame($transaction->id, $job->uniqueId());
    }

    /** @test */
    public function it_implements_should_be_encrypted(): void
    {
        /** @var Transaction */
        $transaction = new Transaction();

        $job = (new SendTransactionJob($transaction));
        $this->assertInstanceOf(ShouldBeEncrypted::class, $job);
    }
}
