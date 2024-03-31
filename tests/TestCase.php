<?php

namespace GloCurrency\AccessBank\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use GloCurrency\AccessBank\Tests\Fixtures\TransactionFixture;
use GloCurrency\AccessBank\Tests\Fixtures\ProcessingItemFixture;
use GloCurrency\AccessBank\Tests\Fixtures\BankFixture;
use GloCurrency\AccessBank\AccessBankServiceProvider;
use GloCurrency\AccessBank\AccessBank;

abstract class TestCase extends OrchestraTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        AccessBank::useTransactionModel(TransactionFixture::class);
        AccessBank::useProcessingItemModel(ProcessingItemFixture::class);
        AccessBank::useBankModel(BankFixture::class);
    }

    protected function getPackageProviders($app)
    {
        return [AccessBankServiceProvider::class];
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
