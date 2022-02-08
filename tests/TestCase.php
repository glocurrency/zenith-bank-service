<?php

namespace GloCurrency\ZenithBank\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use GloCurrency\ZenithBank\Tests\Fixtures\TransactionFixture;
use GloCurrency\ZenithBank\Tests\Fixtures\ProcessingItemFixture;
use GloCurrency\ZenithBank\Tests\Fixtures\BankFixture;
use GloCurrency\ZenithBank\ZenithBankServiceProvider;
use GloCurrency\ZenithBank\ZenithBank;

abstract class TestCase extends OrchestraTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        ZenithBank::useTransactionModel(TransactionFixture::class);
        ZenithBank::useProcessingItemModel(ProcessingItemFixture::class);
        ZenithBank::useBankModel(BankFixture::class);
    }

    protected function getPackageProviders($app)
    {
        return [ZenithBankServiceProvider::class];
    }

    /**
     * Create the HTTP mock for API.
     *
     * @return array<\GuzzleHttp\Handler\MockHandler|\GuzzleHttp\HandlerStack> [$httpMock, $handlerStack]
     */
    protected function mockApiFor(string $class): array
    {
        $httpMock = new \GuzzleHttp\Handler\MockHandler();
        $handlerStack = \GuzzleHttp\HandlerStack::create($httpMock);

        $this->app->when($class)
            ->needs(\GuzzleHttp\ClientInterface::class)
            ->give(function () use ($handlerStack) {
                return new \GuzzleHttp\Client(['handler' => $handlerStack]);
            });

        return [$httpMock, $handlerStack];
    }
}
