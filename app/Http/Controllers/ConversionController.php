<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CurrencyService;
use App\Models\Conversion;
use App\Models\FavoritePair;
use Illuminate\Support\Facades\Log;

class ConversionController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Convert currency
     * 
     * Route: POST /api/convert
     * Body: { "amount": 100, "from": "USD", "to": "EUR" }
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function convert(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
        ]);

        try {
            // Perform conversion using our service
            $result = $this->currencyService->convert(
                $validated['amount'],
                $validated['from'],
                $validated['to']
            );

            // If conversion was successful, save to history
            if ($result['success']) {
                Conversion::create([
                    'from_currency' => $result['from'],
                    'to_currency' => $result['to'],
                    'amount' => $result['amount'],
                    'rate' => $result['rate'],
                    'result' => $result['result'],
                    'ip_address' => $request->ip()
                ]);

                Log::info("Conversion: {$result['formatted']}");
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Conversion error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error performing conversion'
            ], 500);
        }
    }

    /**
     * Get conversion history
     * 
     * Route: GET /api/conversions/history?limit=10
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            
            // Get recent conversions
            $conversions = Conversion::getRecentConversions($limit);

            return response()->json([
                'success' => true,
                'data' => $conversions,
                'count' => $conversions->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching history: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading conversion history'
            ], 500);
        }
    }

    /**
     * Add currency pair to favorites
     * 
     * Route: POST /api/favorites
     * Body: { "from": "USD", "to": "EUR" }
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addFavorite(Request $request)
    {
        $validated = $request->validate([
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
        ]);

        try {
            $sessionId = $request->session()->getId();

            $favorite = FavoritePair::addOrUpdate(
                $validated['from'],
                $validated['to'],
                $sessionId
            );

            return response()->json([
                'success' => true,
                'message' => 'Added to favorites',
                'data' => $favorite
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding favorite: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error adding to favorites'
            ], 500);
        }
    }

    /**
     * Get user's favorite currency pairs
     * 
     * Route: GET /api/favorites
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFavorites(Request $request)
    {
        try {
            $sessionId = $request->session()->getId();
            
            $favorites = FavoritePair::getBySession($sessionId);

            return response()->json([
                'success' => true,
                'data' => $favorites,
                'count' => $favorites->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching favorites: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading favorites'
            ], 500);
        }
    }

    /**
     * Remove from favorites
     * 
     * Route: DELETE /api/favorites/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFavorite($id)
    {
        try {
            $favorite = FavoritePair::findOrFail($id);
            $favorite->delete();

            return response()->json([
                'success' => true,
                'message' => 'Removed from favorites'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Favorite not found'
            ], 404);
        }
    }
}