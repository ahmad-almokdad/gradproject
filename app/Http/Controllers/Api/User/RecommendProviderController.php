<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\Order;
/*
class RecommendProviderController extends Controller
{
    

    public function recommendSimilarProviders($providerId)
    {
        $selectedProvider = Provider::findOrFail($providerId);
    
        $recommendedProviders = Provider::where('id', '!=', $selectedProvider->id)
            ->where('rate', '>=', $selectedProvider->rating - 1)
            ->where('rate', '<=', $selectedProvider->rating + 1)
            ->where('orders_completed', '>=', $selectedProvider->total_orders - 2)
            ->where('orders_completed', '<=', $selectedProvider->total_orders + 2)
            ->get();
    
        return response()->json(['recommended_providers' => $recommendedProviders]);
    }
    
}
    */
