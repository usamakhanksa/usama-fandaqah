<?php

namespace App;

use App\Interfaces\Confirmable;
use App\Interfaces\Customer;
use App\Interfaces\Exchangeable;
use App\Interfaces\WalletFloat;
use App\Services\WalletService;
use App\Traits\CanConfirm;
use App\Traits\CanExchange;
use App\Traits\CanPayFloat;
use App\Traits\HasGift;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Watson\Rememberable\Rememberable;
use function app;
use function array_key_exists;
use function config;

/**
 * Class Wallet
 * @package Bavix\Wallet\Models
 * @property string $slug
 * @property int $balance
 * @property int $decimal_places
 * @property \Bavix\Wallet\Interfaces\Wallet $holder
 * @property-read string $currency
 */
class Wallet extends Model implements Customer, WalletFloat, Confirmable, Exchangeable
{
    use Rememberable;
    use CanConfirm;
    use CanExchange;
    use CanPayFloat;
    use HasGift;

    /**
     * @var array
     */
    protected $fillable = [
        'holder_type',
        'holder_id',
        'name',
        'slug',
        'description',
        'balance',
        'decimal_places',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'balance' => 'int',
        'decimal_places' => 'int',
    ];

    /**
     * @return string
     */
    public function getTable(): string
    {
        if (!$this->table) {
            $this->table = config('wallet.wallet.table');
        }

        return parent::getTable();
    }

    /**
     * @param string $name
     * @return void
     */
    public function setNameAttribute(string $name): void
    {
        $this->attributes['name'] = $name;

        /**
         * Must be updated only if the model does not exist
         *  or the slug is empty
         */
        if (!$this->exists && !array_key_exists('slug', $this->attributes)) {
            $this->attributes['slug'] = Str::slug($name);
        }
    }

    /**
     * @return bool
     */
    public function refreshBalance(): bool
    {
        return app(WalletService::class)->refresh($this);
    }

    /**
     * @return int
     */
    public function getAvailableBalance(): int
    {
        return $this->transactions()
            ->where('wallet_id', $this->getKey())
            ->where('confirmed', true)
            ->sum('amount');
    }

    /**
     * @return MorphTo
     */
    public function holder(): MorphTo
    {
        return $this->morphTo()->withoutGlobalScopes();
    }

    /**
     * @return string
     */
    public function getCurrencyAttribute(): string
    {
        $currencies = config('wallet.currencies', []);
        return $currencies[$this->slug] ?? Str::upper($this->slug);
    }

}
