<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CurrencyService
{
    private $apiKey;
    private $apiUrl;
    private $cacheDuration;

    public function __construct()
    {
        $this->apiKey = config('currency.api_key');
        $this->apiUrl = config('currency.api_url');
        $this->cacheDuration = config('currency.cache_duration'); // 3600 seconds = 1 hour
    }

    /**
     * Fetch all available currencies from API and save to database
     * Also caches the list in memory for 24 hours
     * 
     * @return array
     */
    public function fetchAndStoreCurrencies()
    {
        try {
            $response = Http::get("{$this->apiUrl}/{$this->apiKey}/codes");

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch currencies from API');
            }

            $data = $response->json();
            $supportedCodes = $data['supported_codes'] ?? [];

            $currenciesStored = 0;

            foreach ($supportedCodes as $currencyData) {
                Currency::updateOrCreate(
                    ['code' => $currencyData[0]],
                    [
                        'name' => $currencyData[1],
                        'symbol' => $this->getCurrencySymbol($currencyData[0]),
                        'is_active' => true
                    ]
                );
                $currenciesStored++;
            }

            // Clear the currencies cache so fresh data is loaded next time
            Cache::forget('currencies.all');

            return [
                'success' => true,
                'message' => "Successfully stored {$currenciesStored} currencies",
                'count' => $currenciesStored
            ];

        } catch (\Exception $e) {
            Log::error('Currency fetch error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error fetching currencies: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all active currencies with caching
     * Caches for 24 hours since currencies don't change often
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getAllCurrencies()
    {
        // Cache key: 'currencies.all'
        // Cache for 86400 seconds (24 hours)
        return Cache::remember('currencies.all', 86400, function () {
            Log::info('Loading currencies from database (cache miss)');
            return Currency::getActiveCurrencies();
        });
    }

    /**
     * Get exchange rate with two-layer caching
     * 
     * LAYER 1: Memory cache (fast)
     * LAYER 2: Database cache (medium)
     * LAYER 3: API call (slow)
     * 
     * @param string $from
     * @param string $to
     * @return float|null
     */
    public function getExchangeRate($from, $to)
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        // Create a unique cache key for this currency pair
        $cacheKey = "exchange_rate.{$from}.{$to}";

        // LAYER 1: Check memory cache first (super fast!)
        if (Cache::has($cacheKey)) {
            Log::info("Rate from memory cache: {$from} to {$to}");
            return Cache::get($cacheKey);
        }

        // LAYER 2: Check database cache
        $existingRate = ExchangeRate::getLatestRate($from, $to);
        
        if ($existingRate && $existingRate->isFresh()) {
            Log::info("Rate from database cache: {$from} to {$to}");
            
            // Save to memory cache for next time
            Cache::put($cacheKey, $existingRate->rate, $this->cacheDuration);
            
            return $existingRate->rate;
        }

        // LAYER 3: Fetch from API (slowest, but freshest data)
        Log::info("Fetching fresh rate from API: {$from} to {$to}");
        $rate = $this->fetchExchangeRateFromAPI($from, $to);

        // If we got a rate, cache it in memory
        if ($rate !== null) {
            Cache::put($cacheKey, $rate, $this->cacheDuration);
        }

        return $rate;
    }

    /**
     * Fetch exchange rate from external API and save to database
     * 
     * @param string $from
     * @param string $to
     * @return float|null
     */
    private function fetchExchangeRateFromAPI($from, $to)
    {
        try {
            $response = Http::timeout(10)->get("{$this->apiUrl}/{$this->apiKey}/latest/{$from}");

            if (!$response->successful()) {
                throw new \Exception('API request failed with status: ' . $response->status());
            }

            $data = $response->json();

            if ($data['result'] !== 'success') {
                throw new \Exception('API returned error: ' . ($data['error-type'] ?? 'Unknown'));
            }

            $rates = $data['conversion_rates'] ?? [];
            
            if (!isset($rates[$to])) {
                throw new \Exception("Rate for {$to} not found in API response");
            }

            $rate = $rates[$to];

            // Save to database
            ExchangeRate::create([
                'base_currency' => $from,
                'target_currency' => $to,
                'rate' => $rate,
                'fetched_at' => Carbon::now()
            ]);

            Log::info("Successfully fetched and saved rate: {$from} to {$to} = {$rate}");

            return $rate;

        } catch (\Exception $e) {
            Log::error("API fetch error ({$from} to {$to}): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Convert amount from one currency to another
     * 
     * @param float $amount
     * @param string $from
     * @param string $to
     * @return array
     */
    public function convert($amount, $from, $to)
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        // Validate amount
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => 'Amount must be greater than zero'
            ];
        }

        // Get the exchange rate
        $rate = $this->getExchangeRate($from, $to);

        if ($rate === null) {
            return [
                'success' => false,
                'message' => 'Unable to fetch exchange rate. Please try again.'
            ];
        }

        // Perform calculation
        $result = round($amount * $rate, 2);

        return [
            'success' => true,
            'amount' => $amount,
            'from' => $from,
            'to' => $to,
            'rate' => $rate,
            'result' => $result,
            'formatted' => "{$amount} {$from} = {$result} {$to}"
        ];
    }

    /**
     * Clear all currency-related caches
     * Useful when you want to force fresh data
     * 
     * @return array
     */
    public function clearCache()
    {
        try {
            // Clear all exchange rate caches
            Cache::flush(); // In production, you'd be more selective
            
            Log::info('All currency caches cleared');
            
            return [
                'success' => true,
                'message' => 'Cache cleared successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error clearing cache: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get cache statistics (for debugging/monitoring)
     * 
     * @return array
     */
    public function getCacheStats()
    {
        $stats = [
            'total_rates_cached' => ExchangeRate::count(),
            'rates_last_hour' => ExchangeRate::where('fetched_at', '>=', Carbon::now()->subHour())->count(),
            'oldest_rate' => ExchangeRate::oldest('fetched_at')->first()?->fetched_at,
            'newest_rate' => ExchangeRate::latest('fetched_at')->first()?->fetched_at,
        ];

        return $stats;
    }

    /**
     * Helper method to get currency symbols
     * 
     * @param string $code
     * @return string
     */
    private function getCurrencySymbol($code)
    {
        $symbols = [
            'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥',
            'INR' => '₹', 'AUD' => 'A$', 'CAD' => 'C$', 'CHF' => 'CHF',
            'CNY' => '¥', 'SEK' => 'kr', 'NZD' => 'NZ$', 'KRW' => '₩',
            'SGD' => 'S$', 'HKD' => 'HK$', 'NOK' => 'kr', 'MXN' => '$',
            'BRL' => 'R$', 'RUB' => '₽', 'ZAR' => 'R', 'TRY' => '₺',
        ];

        return $symbols[$code] ?? '';
    }

    /**
     * Get historical rates for charting
     * 
     * @param string $from
     * @param string $to
     * @param int $days
     * @return array
     */
    public function getHistoricalRates($from, $to, $days = 30)
    {
        return ExchangeRate::getHistoricalRates($from, $to, $days);
    }
}