<?php

namespace GloCurrency\ZenithBank\Tests\Feature\Models\Transaction;

use Illuminate\Support\Facades\Event;
use GloCurrency\ZenithBank\Tests\Fixtures\ProcessingItemFixture;
use GloCurrency\ZenithBank\Tests\FeatureTestCase;
use GloCurrency\ZenithBank\Models\Transaction;
use GloCurrency\ZenithBank\Events\TransactionCreatedEvent;

class GetProcessingItemTest extends FeatureTestCase
{
    /** @test */
    public function it_can_get_processing_item(): void
    {
        Event::fake([
            TransactionCreatedEvent::class,
        ]);

        $processingItem = ProcessingItemFixture::factory()->create();

        $ubaTransaction = Transaction::factory()->create([
            'processing_item_id' => $processingItem->id,
        ]);

        $this->assertSame($processingItem->id, $ubaTransaction->fresh()->processingItem->id);
    }
}
