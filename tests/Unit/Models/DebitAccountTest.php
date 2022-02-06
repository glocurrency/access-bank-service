<?php

namespace GloCurrency\AccessBank\Tests\Unit\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use GloCurrency\AccessBank\Tests\TestCase;
use GloCurrency\AccessBank\Models\DebitAccount;
use BrokeYourBike\BaseModels\BaseUuid;

class DebitAccountTest extends TestCase
{
    /** @test */
    public function it_extends_base_model(): void
    {
        $parent = get_parent_class(DebitAccount::class);

        $this->assertSame(BaseUuid::class, $parent);
    }

    /** @test */
    public function it_uses_soft_deletes(): void
    {
        $usedTraits = class_uses(DebitAccount::class);

        $this->assertArrayHasKey(SoftDeletes::class, $usedTraits);
    }
}
