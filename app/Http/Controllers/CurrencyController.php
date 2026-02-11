<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\Log;

class CurrencyController extends Controller
{
    /**
     * Currency service instance
     */
    protected $currencyService;

    /**
     * Constructor - Inject the service
     * This is called "Dependency Injection"
     */
    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Get all active currencies
     * 
     * Route: GET /api/currencies
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $currencies = $this->currencyService->getAllCurrencies();

            return response()->json([
                'success' => true,
                'data' => $currencies,
                'count' => $currencies->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching currencies: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading currencies'
            ], 500);
        }
    }

    /**
     * Fetch and store currencies from API
     * 
     * Route: POST /api/currencies/sync
     * 
     * This is typically called once during setup or when adding new currencies
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync()
    {
        try {
            $result = $this->currencyService->fetchAndStoreCurrencies();

            if ($result['success']) {
                return response()->json($result);
            }

            return response()->json($result, 400);

        } catch (\Exception $e) {
            Log::error('Error syncing currencies: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error syncing currencies'
            ], 500);
        }
    }

    /**
     * Get exchange rate between two currencies
     * 
     * Route: GET /api/currencies/rate?from=USD&to=EUR
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRate(Request $request)
    {
        // Validate input
        $request->validate([
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
        ]);

        try {
            $from = strtoupper($request->from);
            $to = strtoupper($request->to);

            // Check if currencies are the same
            if ($from === $to) {
                return response()->json([
                    'success' => true,
                    'from' => $from,
                    'to' => $to,
                    'rate' => 1.0,
                    'message' => 'Same currency, rate is 1.0'
                ]);
            }

            $rate = $this->currencyService->getExchangeRate($from, $to);

            if ($rate === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to fetch exchange rate'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'from' => $from,
                'to' => $to,
                'rate' => $rate
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting rate: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching exchange rate'
            ], 500);
        }
    }

    /**
     * Get cache statistics
     * 
     * Route: GET /api/currencies/cache-stats
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function cacheStats()
    {
        try {
            $stats = $this->currencyService->getCacheStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cache stats'
            ], 500);
        }
    }

    /**
     * Clear currency cache
     * 
     * Route: POST /api/currencies/clear-cache
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache()
    {
        try {
            $result = $this->currencyService->clearCache();

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache'
            ], 500);
        }
    }
}