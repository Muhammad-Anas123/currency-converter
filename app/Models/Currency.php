<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all active currencies for dropdown
     */
    public static function getActiveCurrencies()
    {
        return self::where('is_active', true)
                   ->orderBy('code')
                   ->get();
    }

    /**
     * Get currency by code
     */
    public static function findByCode($code)
    {
        return self::where('code', strtoupper($code))->first();
    }

    /**
     * Relationship: Currency has many exchange rates as base currency
     */
    public function baseExchangeRates()
    {
        return $this->hasMany(ExchangeRate::class, 'base_currency', 'code');
    }

    /**
     * Relationship: Currency has many exchange rates as target currency
     */
    public function targetExchangeRates()
    {
        return $this->hasMany(ExchangeRate::class, 'target_currency', 'code');
    }

    /**
     * Relationship: Currency has many conversions as source
     */
    public function conversionsFrom()
    {
        return $this->hasMany(Conversion::class, 'from_currency', 'code');
    }

    /**
     * Relationship: Currency has many conversions as target
     */
    public function conversionsTo()
    {
        return $this->hasMany(Conversion::class, 'to_currency', 'code');
    }
}