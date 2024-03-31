<?php

namespace GloCurrency\AccessBank\Tests\Feature\Jobs;

use Money\Money;
use Money\Currency;
use Illuminate\Support\Facades\Event;
use GloCurrency\MiddlewareBlocks\Enums\TransactionTypeEnum as MTransactionTypeEnum;
use GloCurrency\MiddlewareBlocks\Enums\TransactionStateCodeEnum as MTransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Contracts\TransactionInterface as MTransactionInterface;
use GloCurrency\MiddlewareBlocks\Contracts\SenderInterface as MSenderInterface;
use GloCurrency\MiddlewareBlocks\Contracts\RecipientInterface as MRecipientInterface;
use GloCurrency\MiddlewareBlocks\Contracts\ProcessingItemInterface as MProcessingItemInterface;
use GloCurrency\AccessBank\Tests\Fixtures\BankFixture;
use GloCurrency\AccessBank\Tests\FeatureTestCase;
use GloCurrency\AccessBank\Models\Transaction;
use GloCurrency\AccessBank\Models\DebitAccount;
use GloCurrency\AccessBank\Models\Bank;
use GloCurrency\AccessBank\Jobs\CreateTransactionJob;
use GloCurrency\AccessBank\Exceptions\CreateTransactionException;
use GloCurrency\AccessBank\Events\TransactionUpdatedEvent;
use GloCurrency\AccessBank\Events\TransactionCreatedEvent;
use GloCurrency\AccessBank\Enums\TransactionStateCodeEnum;

class CreateTransactionJobTest extends FeatureTestCase
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

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_if_access_bank_transaction_already_exist(): void
    {
        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
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

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_transaction_sender(): void
    {
        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn(null);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        $this->expectExceptionMessage('sender not found');
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn(null);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        $this->expectExceptionMessage('recipient not found');
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_bank_code_in_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getId')->willReturn($this->faker()->uuid());
        $recipient->method('getBankCode')->willReturn(null);

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /** @var MRecipientInterface $recipient */
        $this->expectExceptionMessage("`{$recipient->getId()}` has no `bank_code`");
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_bank_account_in_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getId')->willReturn($this->faker()->uuid());
        $recipient->method('getBankCode')->willReturn($this->faker()->word());
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

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_if_debit_account_not_found(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getCountryCode')->willReturn('NGA');
        $recipient->method('getBankCode')->willReturn('111111');
        $recipient->method('getBankAccount')->willReturn('123');

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);
        $transaction->method('getOutputAmount')->willReturn(new Money('100', new Currency('USD')));

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /** @var DebitAccount $debitAccount */
        $debitAccount = DebitAccount::factory()->create([
            'country_code' => 'NGA',
            'currency_code' => 'USD',
        ]);
        $debitAccount->delete();

        $this->expectExceptionMessage('DebitAccount for NGA/USD not found');
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /**
     * @test
     * @dataProvider transactionStatesProvider
     */
    public function create_transaction_with_state(MTransactionStateCodeEnum $stateCode, bool $shouldThrow): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getCountryCode')->willReturn($this->faker->countryISOAlpha3());
        $recipient->method('getBankCode')->willReturn($this->faker->unique()->word());
        $recipient->method('getBankAccount')->willReturn($this->faker->numerify('##########'));

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn($stateCode);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);
        $transaction->method('getOutputAmount')->willReturn(new Money('200', new Currency('USD')));

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /**
         * @var MRecipientInterface $recipient
         * @var MTransactionInterface $transaction
         */
        DebitAccount::factory()->create([
            'country_code' => $recipient->getCountryCode(),
            'currency_code' => $transaction->getOutputAmount()->getCurrency()->getCode(),
        ]);

        $mainBank = BankFixture::factory()->create([
            'country_code' => $recipient->getCountryCode(),
            'code' => $recipient->getBankCode(),
        ]);

        Bank::factory()->create([
            'bank_id' => $mainBank->id,
        ]);

        $this->assertNull(Transaction::first());

        try {
            CreateTransactionJob::dispatchSync($processingItem);
        } catch (\Throwable $th) {
            $this->assertInstanceOf(CreateTransactionException::class, $th);
            $this->assertStringContainsString("state_code `{$stateCode->value}` not allowed", $th->getMessage());
            $this->assertNull(Transaction::first());
            return;
        }

        if ($shouldThrow) {
            $this->fail('Exception was not thrown');
        }

        $this->assertNotNull(Transaction::first());
    }

    public function transactionStatesProvider(): array
    {
        $states = collect(MTransactionStateCodeEnum::cases())
            ->filter(fn($c) => !in_array($c, [
                MTransactionStateCodeEnum::PROCESSING,
            ]))
            ->flatten()
            ->map(fn($c) => [$c, true])
            ->toArray();

        $states[] = [MTransactionStateCodeEnum::PROCESSING, false];

        return $states;
    }

    /**
     * @test
     * @dataProvider transactionTypesProvider
     */
    public function create_transaction_with_type(MTransactionTypeEnum $type, bool $shouldThrow): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getCountryCode')->willReturn($this->faker->countryISOAlpha3());
        $recipient->method('getBankCode')->willReturn($this->faker->unique()->word());
        $recipient->method('getBankAccount')->willReturn($this->faker->numerify('##########'));

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn($type);
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
        DebitAccount::factory()->create([
            'country_code' => $recipient->getCountryCode(),
            'currency_code' => $transaction->getOutputAmount()->getCurrency()->getCode(),
        ]);

        $mainBank = BankFixture::factory()->create([
            'country_code' => $recipient->getCountryCode(),
            'code' => $recipient->getBankCode(),
        ]);

        Bank::factory()->create([
            'bank_id' => $mainBank->id,
        ]);

        $this->assertNull(Transaction::first());

        try {
            CreateTransactionJob::dispatchSync($processingItem);
        } catch (\Throwable $th) {
            $this->assertInstanceOf(CreateTransactionException::class, $th);
            $this->assertStringContainsString("type `{$type->value}` not allowed", $th->getMessage());
            $this->assertNull(Transaction::first());
            return;
        }

        if ($shouldThrow) {
            $this->fail('Exception was not thrown');
        }

        $this->assertNotNull(Transaction::first());
    }

    public function transactionTypesProvider(): array
    {
        $types = collect(MTransactionTypeEnum::cases())
            ->filter(fn($c) => !in_array($c, [
                MTransactionTypeEnum::BANK,
            ]))
            ->flatten()
            ->map(fn($c) => [$c, true])
            ->toArray();

        $types[] = [MTransactionTypeEnum::BANK, false];

        return $types;
    }

    /** @test */
    public function it_can_create_transaction(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getCountryCode')->willReturn($this->faker->countryISOAlpha3());
        $recipient->method('getBankCode')->willReturn($this->faker->unique()->word());
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
         * @var MProcessingItemInterface $processingItem
        */

        $debitAccount = DebitAccount::factory()->create([
            'country_code' => $recipient->getCountryCode(),
            'currency_code' => $transaction->getOutputAmount()->getCurrency()->getCode(),
        ]);

        $mainBank = BankFixture::factory()->create([
            'country_code' => $recipient->getCountryCode(),
            'code' => $recipient->getBankCode(),
        ]);

        Bank::factory()->create([
            'bank_id' => $mainBank->id,
        ]);

        $this->assertNull(Transaction::first());

        CreateTransactionJob::dispatchSync($processingItem);

        $this->assertNotNull($targetTransaction = Transaction::first());
        $this->assertSame($transaction->getId(), $targetTransaction->transaction_id);
        $this->assertSame($processingItem->getId(), $targetTransaction->processing_item_id);
        $this->assertEquals(TransactionStateCodeEnum::LOCAL_UNPROCESSED, $targetTransaction->state_code);
        $this->assertEquals($debitAccount->account_number, $targetTransaction->debit_account);
        $this->assertSame($transaction->getReferenceForHumans(), $targetTransaction->reference);
    }
}
