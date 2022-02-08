<?php

namespace GloCurrency\ZenithBank\Jobs;

use Illuminate\Support\Facades\App;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Bus\Queueable;
use GloCurrency\ZenithBank\Models\Transaction;
use GloCurrency\ZenithBank\Exceptions\SendTransactionException;
use GloCurrency\ZenithBank\Enums\TransactionStateCodeEnum;
use GloCurrency\ZenithBank\Enums\ErrorCodeFactory;
use GloCurrency\MiddlewareBlocks\Enums\QueueTypeEnum as MQueueTypeEnum;
use BrokeYourBike\ZenithBank\Enums\PostedStatusEnum;
use BrokeYourBike\ZenithBank\Enums\ErrorCodeEnum;
use BrokeYourBike\ZenithBank\Client;

class SendTransactionJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    private Transaction $targetTransaction;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Transaction $targetTransaction)
    {
        $this->targetTransaction = $targetTransaction;
        $this->afterCommit();
        $this->onQueue(MQueueTypeEnum::SERVICES->value);
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->targetTransaction->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (TransactionStateCodeEnum::LOCAL_UNPROCESSED !== $this->targetTransaction->state_code) {
            throw SendTransactionException::stateNotAllowed($this->targetTransaction);
        }

        try {
            /** @var Client */
            $api = App::make(Client::class);
            $response = $api->sendDomesticTransaction($this->targetTransaction);
        } catch (\Throwable $e) {
            report($e);
            throw SendTransactionException::apiRequestException($e);
        }

        $errorCode = ErrorCodeEnum::tryFrom($response->responseCode);

        if (!$errorCode) {
            throw SendTransactionException::unexpectedErrorCode($response->responseCode);
        }

        $this->targetTransaction->error_code = $errorCode;
        $this->targetTransaction->state_code = ErrorCodeFactory::getTransactionStateCode($errorCode);

        // TODO: validate?
        // TODO: get `state_code` based on `posted_status` and `error_code` ?
        if ($response->posted && $postedStatus = PostedStatusEnum::tryFrom($response->posted)) {
            $this->targetTransaction->posted_status = $postedStatus;
        }

        $this->targetTransaction->error_code_description = $response->responseDescription;
        $this->targetTransaction->save();
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        report($exception);

        if ($exception instanceof SendTransactionException) {
            $this->targetTransaction->update([
                'state_code' => $exception->getStateCode(),
                'state_code_reason' => $exception->getStateCodeReason(),
            ]);
            return;
        }

        $this->targetTransaction->update([
            'state_code' => TransactionStateCodeEnum::LOCAL_EXCEPTION,
            'state_code_reason' => $exception->getMessage(),
        ]);
    }
}
