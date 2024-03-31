<?php

namespace GloCurrency\AccessBank\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use GloCurrency\AccessBank\Database\Factories\BankFactory;
use BrokeYourBike\BaseModels\BaseUuid;

/**
 * GloCurrency\AccessBank\Models\Bank
 *
 * @property string $id
 * @property string $bank_id
 * @property string $codename
 * @property bool $domestic
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Bank extends BaseUuid
{
    use HasFactory;

    protected $table = 'access_banks';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<mixed>
     */
    protected $casts = [
        'domestic' => 'boolean',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return BankFactory::new();
    }
}
