<?php

namespace GloCurrency\AccessBank\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use BrokeYourBike\BaseModels\BaseUuid;

/**
 * GloCurrency\AccessBank\Models\DebitAccount
 *
 * @property string $id
 * @property string $account_number
 * @property string $name
 * @property string $country_code
 * @property string $currency_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class DebitAccount extends BaseUuid
{
    use HasFactory;

    protected $table = 'access_debit_accounts';
}
