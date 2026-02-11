<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'base_currency',
        'target_currency',
        'rate',
        'fetched_at'
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'fetched_at' => 'datetime',
    ];

    /**
     * Get the latest rate between two currencies
     */
    public static function getLatestRate($baseCurrency, $targetCurrency)
    {
        return self::where('base_currency', strtoupper($baseCurrency))
                   ->where('target_currency', strtoupper($targetCurrency))
                   ->orderBy('fetched_at', 'desc')
                   ->first();
    }

    /**
     * Check if rate is still fresh (less than 1 hour old)
     */
    public function isFresh()
    {
        $cacheMinutes = config('currency.cache_duration') / 60;
        return $this->fetched_at->diffInMinutes(now()) < $cacheMinutes;
    }

    /**
     * Get historical rates for a currency pair
     */
    public static function getHistoricalRates($baseCurrency, $targetCurrency, $days = 30)
    {
        return self::where('base_currency', strtoupper($baseCurrency))
                   ->where('target_currency', strtoupper($targetCurrency))
                   ->where('fetched_at', '>=', Carbon::now()->subDays($days))
                   ->orderBy('fetched_at', 'asc')
                   ->get();
    }

    /**
     * Relationship: Exchange rate belongs to base currency
     */
    public function baseCurrency()
    {
        return $this->belongsTo(Currency::class, 'base_currency', 'code');
    }

    /**
     * Relationship: Exchange rate belongs to target currency
     */
    public function targetCurrency()
    {
        return $this->belongsTo(Currency::class, 'target_currency', 'code');
    }
}