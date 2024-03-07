<?php

namespace GloCurrency\ZenithBank\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GloCurrency\ZenithBank\ZenithBank;
use GloCurrency\ZenithBank\Events\TransactionUpdatedEvent;
use GloCurrency\ZenithBank\Events\TransactionCreatedEvent;
use GloCurrency\ZenithBank\Enums\TransactionStateCodeEnum;
use GloCurrency\ZenithBank\Database\Factories\TransactionFactory;
use GloCurrency\MiddlewareBlocks\Contracts\ModelWithStateCodeInterface;
use BrokeYourBike\ZenithBank\Interfaces\TransactionInterface;
use BrokeYourBike\ZenithBank\Enums\PostedStatusEnum;
use BrokeYourBike\ZenithBank\Enums\ErrorCodeEnum;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\BaseModels\BaseUuid;

/**
 * GloCurrency\ZenithBank\Models\Transaction
 *
 * @property string $id
 * @property string $transaction_id
 * @property string $processing_item_id
 * @property \GloCurrency\ZenithBank\Enums\TransactionStateCodeEnum $state_code
 * @property string|null $state_code_reason
 * @property \BrokeYourBike\ZenithBank\Enums\ErrorCodeEnum|null $error_code
 * @property string|null $error_code_description
 * @property \BrokeYourBike\ZenithBank\Enums\PostedStatusEnum|null $posted_status
 * @property string $reference
 * @property string $debit_account
 * @property string $sender_name
 * @property string $recipient_account
 * @property string $recipient_bank_code
 * @property string $recipient_name
 * @property string $currency_code
 * @property float $amount
 * @property bool $should_resend
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Transaction extends BaseUuid implements ModelWithStateCodeInterface, SourceModelInterface, TransactionInterface
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'zenith_transactions';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<mixed>
     */
    protected $casts = [
        'state_code' => TransactionStateCodeEnum::class,
        'error_code' => ErrorCodeEnum::class,
        'posted_status' => PostedStatusEnum::class,
        'amount' => 'double',
        'should_resend' => 'boolean',
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

    public function getSenderName(): string
    {
        return $this->sender_name;
    }

    public function getRecipientName(): string
    {
        return $this->recipient_name;
    }

    public function getRecipientAccount(): string
    {
        return $this->recipient_account;
    }
    
    public function getRecipientBankCode(): string
    {
        return $this->recipient_bank_code;
    }

    public function getDebitAccount(): string
    {
        return $this->debit_account;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function shouldResend(): bool
    {
        return $this->should_resend;
    }

    /**
     * The ProcessingItem that Transaction belong to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function processingItem()
    {
        return $this->belongsTo(ZenithBank::$processingItemModel, 'processing_item_id', 'id');
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
