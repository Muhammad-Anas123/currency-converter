<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_currency',
        'to_currency',
        'amount',
        'rate',
        'result',
        'ip_address'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'rate' => 'decimal:8',
        'result' => 'decimal:2',
    ];

    /**
     * Get recent conversions (last 10)
     */
    public static function getRecentConversions($limit = 10)
    {
        return self::orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get conversions by session
     */
    public static function getBySession($sessionId, $limit = 10)
    {
        return self::where('ip_address', $sessionId)
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Relationship: Conversion belongs to source currency
     */
    public function fromCurrency()
    {
        return $this->belongsTo(Currency::class, 'from_currency', 'code');
    }

    /**
     * Relationship: Conversion belongs to target currency
     */
    public function toCurrency()
    {
        return $this->belongsTo(Currency::class, 'to_currency', 'code');
    }

    /**
     * Format conversion for display
     */
    public function getFormattedConversionAttribute()
    {
        return "{$this->amount} {$this->from_currency} = {$this->result} {$this->to_currency}";
    }
}