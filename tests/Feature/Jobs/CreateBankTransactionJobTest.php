<?php

namespace GloCurrency\ZenithBank\Tests\Feature\Jobs;

use Money\Money;
use Money\Currency;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Carbon;
use GloCurrency\ZenithBank\Tests\FeatureTestCase;
use GloCurrency\ZenithBank\Models\Transaction;
use GloCurrency\ZenithBank\Models\DebitAccount;
use GloCurrency\ZenithBank\Jobs\CreateBankTransactionJob;
use GloCurrency\ZenithBank\Exceptions\CreateTransactionException;
use GloCurrency\ZenithBank\Events\TransactionUpdatedEvent;
use GloCurrency\ZenithBank\Events\TransactionCreatedEvent;
use GloCurrency\ZenithBank\Enums\TransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Enums\TransactionTypeEnum as MTransactionTypeEnum;
use GloCurrency\MiddlewareBlocks\Enums\TransactionStateCodeEnum as MTransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Contracts\TransactionInterface as MTransactionInterface;
use GloCurrency\MiddlewareBlocks\Contracts\SenderInterface as MSenderInterface;
use GloCurrency\MiddlewareBlocks\Contracts\RecipientInterface as MRecipientInterface;
use GloCurrency\MiddlewareBlocks\Contracts\ProcessingItemInterface as MProcessingItemInterface;

class CreateBankTransactionJobTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TransactionCreatedEvent::class,
            TransactionUpdatedEvent::class,
        ]);
    }

    /** @test */
    public function it_will_throw_without_transaction(): void
    {
        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn(null);

        $this->expectExceptionMessage("transaction not found");
        $this->expectException(CreateTransactionException::class);

        CreateBankTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_if_target_transaction_already_exist(): void
    {
        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /** @var MTransactionInterface $transaction */
        $targetTransaction = Transaction::factory()->create([
            'transaction_id' => $transaction->getId(),
        ]);

        $this->expectExceptionMessage("Transaction cannot be created twice, `{$targetTransaction->id}`");
        $this->expectException(CreateTransactionException::class);

        CreateBankTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_transaction_sender(): void
    {
        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn(null);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        $this->expectExceptionMessage('sender not found');
        $this->expectException(CreateTransactionException::class);

        CreateBankTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn(null);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        $this->expectExceptionMessage('recipient not found');
        $this->expectException(CreateTransactionException::class);

        CreateBankTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_bank_account_in_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getBankCode')->willReturn($this->faker->word());
        $recipient->method('getBankAccount')->willReturn(null);

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /** @var MRecipientInterface $recipient */
        $this->expectExceptionMessage("`{$recipient->getId()}` has no `bank_account`");
        $this->expectException(CreateTransactionException::class);

        CreateBankTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_if_debit_account_not_found(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $sender->method('getBirthDate')->willReturn(Carbon::now()->subYears(20));

        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getCountryCode')->willReturn('NGA');
        $recipient->method('getBankCode')->willReturn($this->faker->word());
        $recipient->method('getBankAccount')->willReturn($this->faker->numerify('##########'));

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);
        $transaction->method('getOutputAmount')->willReturn(new Money('200', new Currency('USD')));

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /**
         * @var MRecipientInterface $recipient
         * @var MTransactionInterface $transaction
        */

        /** @var DebitAccount */
        $debitAccount = DebitAccount::factory()->create([
            'country_code' => $recipient->getCountryCode(),
            'currency_code' => $transaction->getOutputAmount()->getCurrency()->getCode(),
        ]);
        $debitAccount->delete();

        $this->expectExceptionMessage('DebitAccount for NGA/USD not found');
        $this->expectException(CreateTransactionException::class);

        CreateBankTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_can_create_transaction(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $sender->method('getBirthDate')->willReturn(Carbon::now()->subYears(20));

        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getCountryCode')->willReturn($this->faker->countryISOAlpha3());
        $recipient->method('getBankCode')->willReturn($this->faker->word());
        $recipient->method('getBankAccount')->willReturn($this->faker->numerify('##########'));

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);
        $transaction->method('getOutputAmount')->willReturn(new Money('200', new Currency('USD')));

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /**
         * @var MSenderInterface $sender
         * @var MRecipientInterface $recipient
         * @var MTransactionInterface $transaction
         * @var MProcessingItemInterface $processingItem
        */

        /** @var DebitAccount */
        $debitAccount = DebitAccount::factory()->create([
            'country_code' => $recipient->getCountryCode(),
            'currency_code' => $transaction->getOutputAmount()->getCurrency()->getCode(),
        ]);

        $this->assertNull(Transaction::first());

        CreateBankTransactionJob::dispatchSync($processingItem);

        $this->assertNotNull($targetTransaction = Transaction::first());
        $this->assertSame($transaction->getId(), $targetTransaction->transaction_id);
        $this->assertSame($processingItem->getId(), $targetTransaction->processing_item_id);
        $this->assertEquals(TransactionStateCodeEnum::LOCAL_UNPROCESSED, $targetTransaction->state_code);
        $this->assertSame(null, $targetTransaction->posted_status);
        $this->assertSame($debitAccount->account_number, $targetTransaction->debit_account);
        $this->assertSame($sender->getName(), $targetTransaction->sender_name);
        $this->assertSame($recipient->getBankAccount(), $targetTransaction->recipient_account);
        $this->assertSame($recipient->getName(), $targetTransaction->recipient_name);
        $this->assertSame(2.00, $targetTransaction->amount);
        $this->assertSame(false, $targetTransaction->should_resend);
        $this->assertSame($transaction->getReferenceForHumans(), $targetTransaction->reference);
    }
}
