<?php

namespace GloCurrency\AccessBank\Tests\Feature\Models;

use Illuminate\Support\Facades\Event;
use GloCurrency\AccessBank\Tests\Fixtures\ProcessingItemFixture;
use GloCurrency\AccessBank\Tests\FeatureTestCase;
use GloCurrency\AccessBank\Models\Transaction;

class GetProcessingItemTest extends FeatureTestCase
{
    /** @test */
    public function it_can_get_processing_item(): void
    {
        Event::fake([
            BankTransactionCreatedEvent::class,
        ]);

        $processingItem = ProcessingItemFixture::factory()->create();

        $targetTransaction = Transaction::factory()->create([
            'processing_item_id' => $processingItem->id,
        ]);

        $this->assertSame($processingItem->id, $targetTransaction->fresh()->processingItem->id);
    }
}
