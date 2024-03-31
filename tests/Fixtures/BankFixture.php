<?php

namespace GloCurrency\AccessBank\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use GloCurrency\AccessBank\Tests\Database\Factories\BankFixtureFactory;
use BrokeYourBike\BaseModels\BaseUuid;

class BankFixture extends BaseUuid
{
    use HasFactory;

    protected $table = 'banks';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return BankFixtureFactory::new();
    }
}
