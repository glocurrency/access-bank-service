<?php

namespace GloCurrency\AccessBank\Tests\Unit\Jobs;

use PHPUnit\Framework\TestCase;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use GloCurrency\AccessBank\Models\Transaction;
use GloCurrency\AccessBank\Jobs\FetchTransactionUpdateJob;

class FetchTransactionUpdateJobTest extends TestCase
{
    /** @test */
    public function it_has_tries_defined(): void
    {
        $transaction = new Transaction();

        $job = (new FetchTransactionUpdateJob($transaction));
        $this->assertSame(1, $job->tries);
    }

    /** @test */
    public function it_implements_should_be_unique(): void
    {
        $transaction = new Transaction();

        $job = (new FetchTransactionUpdateJob($transaction));
        $this->assertInstanceOf(ShouldBeUnique::class, $job);
        $this->assertSame($transaction->id, $job->uniqueId());
    }

    /** @test */
    public function it_implements_should_be_encrypted(): void
    {
        $transaction = new Transaction();

        $job = (new FetchTransactionUpdateJob($transaction));
        $this->assertInstanceOf(ShouldBeEncrypted::class, $job);
    }
}
