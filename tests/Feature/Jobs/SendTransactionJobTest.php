<?php

namespace GloCurrency\ZenithBank\Tests\Feature\Jobs;

use Illuminate\Support\Facades\Event;
use GloCurrency\ZenithBank\Tests\FeatureTestCase;
use GloCurrency\ZenithBank\Models\Transaction;
use GloCurrency\ZenithBank\Jobs\SendTransactionJob;
use GloCurrency\ZenithBank\Exceptions\SendTransactionException;
use GloCurrency\ZenithBank\Events\TransactionUpdatedEvent;
use GloCurrency\ZenithBank\Events\TransactionCreatedEvent;
use GloCurrency\ZenithBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\ZenithBank\Enums\PostedStatusEnum;
use BrokeYourBike\ZenithBank\Enums\ErrorCodeEnum;
use BrokeYourBike\ZenithBank\Client;

class SendTransactionJobTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TransactionCreatedEvent::class,
            TransactionUpdatedEvent::class,
        ]);
    }

    private function makeAuthResponse(): \GuzzleHttp\Psr7\Response
    {
        return new \GuzzleHttp\Psr7\Response(200, [], '{
            "responseCode": "00",
            "responseDescription": "SUCCESS",
            "description": null,
            "tokenDetail": {
                "token": "super.secure.string",
                "expiration": "'. now()->addHour() .'"
            }
        }');
    }

    /** @test @todo use all states */
    public function it_will_throw_if_state_not_LOCAL_UNPROCESSED(): void
    {
        /** @var Transaction */
        $zenithTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::PAID,
        ]);

        $this->expectExceptionMessage("Transaction state_code `{$zenithTransaction->state_code->value}` not allowed");
        $this->expectException(SendTransactionException::class);

        SendTransactionJob::dispatchSync($zenithTransaction);
    }

    /** @test */
    public function it_can_send_usd_transaction(): void
    {
        /** @var Transaction */
        $zenithTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
            'currency_code' => 'USD',
        ]);

        [$httpMock, $stack] = $this->mockApiFor(Client::class);
        $httpMock->append($this->makeAuthResponse());
        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{
            "responseCode": "' . ErrorCodeEnum::SUCCESS->value . '",
            "responseDescription": "Money on the way!",
            "transactionReference": "' . $zenithTransaction->id . '",
            "posted": "' . PostedStatusEnum::YES->value . '",
            "postingDate": "' . (string) now() . '",
            "postingReference": "LOL-123"
        }'));

        SendTransactionJob::dispatchSync($zenithTransaction);

        /** @var Transaction */
        $zenithTransaction = $zenithTransaction->fresh();

        $this->assertEquals(TransactionStateCodeEnum::PAID, $zenithTransaction->state_code);
        $this->assertEquals(PostedStatusEnum::YES, $zenithTransaction->posted_status);
        $this->assertEquals(ErrorCodeEnum::SUCCESS, $zenithTransaction->error_code);
        $this->assertSame('Money on the way!', $zenithTransaction->error_code_description);
    }

    /** @test */
    public function it_can_send_ngn_other_bank_transaction(): void
    {
        /** @var Transaction */
        $zenithTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
            'currency_code' => 'USD',
            'recipient_bank_code' => '111',
        ]);

        [$httpMock, $stack] = $this->mockApiFor(Client::class);
        $httpMock->append($this->makeAuthResponse());
        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{
            "responseCode": "' . ErrorCodeEnum::SUCCESS->value . '",
            "responseDescription": "Money on the way!",
            "transactionReference": "' . $zenithTransaction->id . '",
            "posted": "' . PostedStatusEnum::YES->value . '",
            "postingDate": "' . (string) now() . '",
            "postingReference": "LOL-123"
        }'));

        SendTransactionJob::dispatchSync($zenithTransaction);

        /** @var Transaction */
        $zenithTransaction = $zenithTransaction->fresh();

        $this->assertEquals(TransactionStateCodeEnum::PAID, $zenithTransaction->state_code);
        $this->assertEquals(PostedStatusEnum::YES, $zenithTransaction->posted_status);
        $this->assertEquals(ErrorCodeEnum::SUCCESS, $zenithTransaction->error_code);
        $this->assertSame('Money on the way!', $zenithTransaction->error_code_description);
    }

    /** @test */
    public function it_can_send_ngn_domestic_transaction(): void
    {
        /** @var Transaction */
        $zenithTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
            'currency_code' => 'USD',
            'recipient_bank_code' => '057',
        ]);

        [$httpMock, $stack] = $this->mockApiFor(Client::class);
        $httpMock->append($this->makeAuthResponse());
        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{
            "responseCode": "' . ErrorCodeEnum::SUCCESS->value . '",
            "responseDescription": "Money on the way!",
            "transactionReference": "' . $zenithTransaction->id . '",
            "posted": "' . PostedStatusEnum::YES->value . '",
            "postingDate": "' . (string) now() . '",
            "postingReference": "LOL-123"
        }'));

        SendTransactionJob::dispatchSync($zenithTransaction);

        /** @var Transaction */
        $zenithTransaction = $zenithTransaction->fresh();

        $this->assertEquals(TransactionStateCodeEnum::PAID, $zenithTransaction->state_code);
        $this->assertEquals(PostedStatusEnum::YES, $zenithTransaction->posted_status);
        $this->assertEquals(ErrorCodeEnum::SUCCESS, $zenithTransaction->error_code);
        $this->assertSame('Money on the way!', $zenithTransaction->error_code_description);
    }
}
