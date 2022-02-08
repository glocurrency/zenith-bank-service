<?php

namespace GloCurrency\ZenithBank\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GloCurrency\ZenithBank\Database\Factories\DebitAccountFactory;
use BrokeYourBike\BaseModels\BaseUuid;

/**
 * GloCurrency\ZenithBank\Models\DebitAccount
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
    use SoftDeletes;

    protected $table = 'zenith_debit_accounts';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return DebitAccountFactory::new();
    }
}
