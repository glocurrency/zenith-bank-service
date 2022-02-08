<?php

namespace GloCurrency\ZenithBank;

final class ZenithBank
{
    /**
     * Indicates if ZenithBank migrations will be run.
     *
     * @var bool
     */
    public static $runsMigrations = true;

    /**
     * The default Transaction model class name.
     *
     * @var string
     */
    public static $transactionModel = 'App\\Models\\Transaction';

    /**
     * The default ProcessingItem model class name.
     *
     * @var string
     */
    public static $processingItemModel = 'App\\Models\\ProcessingItem';

    /**
     * The default Bank model class name.
     *
     * @var string
     */
    public static $bankModel = 'App\\Models\\Bank';

    /**
     * Configure ZenithBank to not register its migrations.
     *
     * @return static
     */
    public static function ignoreMigrations()
    {
        static::$runsMigrations = false;

        return new static;
    }

    /**
     * Set the Transaction model class name.
     *
     * @param  string  $transactionModel
     * @return void
     */
    public static function useTransactionModel($transactionModel)
    {
        static::$transactionModel = $transactionModel;
    }

    /**
     * Set the ProcessingItem model class name.
     *
     * @param  string  $processingItemModel
     * @return void
     */
    public static function useProcessingItemModel($processingItemModel)
    {
        static::$processingItemModel = $processingItemModel;
    }

    /**
     * Set the Bank model class name.
     *
     * @param  string  $bankModel
     * @return void
     */
    public static function useBankModel($bankModel)
    {
        static::$bankModel = $bankModel;
    }
}
