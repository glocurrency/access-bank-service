<?php

namespace GloCurrency\AccessBank\Tests\Unit\Enums;

use GloCurrency\MiddlewareBlocks\Enums\ProcessingItemStateCodeEnum as MProcessingItemStateCodeEnum;
use GloCurrency\AccessBank\Tests\TestCase;
use GloCurrency\AccessBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\AccessBank\Enums\StatusCodeEnum;
use BrokeYourBike\AccessBank\Enums\ErrorCodeEnum;

class TransactionStateCodeTest extends TestCase
{
    /** @test */
    public function it_can_be_created_from_error_codes()
    {
        foreach (ErrorCodeEnum::cases() as $errorCode) {
            $this->assertInstanceOf(TransactionStateCodeEnum::class, TransactionStateCodeEnum::makeFromErrorCode($errorCode));
        }
    }

    /** @test */
    public function it_can_be_created_from_status_codes()
    {
        foreach (StatusCodeEnum::cases() as $statusCode) {
            $this->assertInstanceOf(TransactionStateCodeEnum::class, TransactionStateCodeEnum::makeFromStatusCode($statusCode));
        }
    }

    /** @test */
    public function it_can_return_processing_item_state_code_from_all_values()
    {
        foreach (TransactionStateCodeEnum::cases() as $value) {
            $this->assertInstanceOf(MProcessingItemStateCodeEnum::class, $value->getProcessingItemStateCode());
        }
    }
}
