<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoritePair extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_currency',
        'to_currency',
        'session_id',
        'usage_count'
    ];

    protected $casts = [
        'usage_count' => 'integer',
    ];

    /**
     * Get favorite pairs for a session
     */
    public static function getBySession($sessionId)
    {
        return self::where('session_id', $sessionId)
                   ->orderBy('usage_count', 'desc')
                   ->get();
    }

    /**
     * Add or update favorite pair
     */
    public static function addOrUpdate($fromCurrency, $toCurrency, $sessionId)
    {
        $favorite = self::firstOrCreate(
            [
                'from_currency' => strtoupper($fromCurrency),
                'to_currency' => strtoupper($toCurrency),
                'session_id' => $sessionId
            ],
            [
                'usage_count' => 0
            ]
        );

        $favorite->increment('usage_count');
        
        return $favorite;
    }

    /**
     * Check if pair is favorited
     */
    public static function isFavorited($fromCurrency, $toCurrency, $sessionId)
    {
        return self::where('from_currency', strtoupper($fromCurrency))
                   ->where('to_currency', strtoupper($toCurrency))
                   ->where('session_id', $sessionId)
                   ->exists();
    }

    /**
     * Relationship: Favorite pair belongs to source currency
     */
    public function fromCurrency()
    {
        return $this->belongsTo(Currency::class, 'from_currency', 'code');
    }

    /**
     * Relationship: Favorite pair belongs to target currency
     */
    public function toCurrency()
    {
        return $this->belongsTo(Currency::class, 'to_currency', 'code');
    }
}