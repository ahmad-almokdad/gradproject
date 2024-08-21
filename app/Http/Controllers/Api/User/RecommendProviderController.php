<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\Order;

class RecommendProviderController extends Controller
{
    
    public function recommendSimilarProviders($providerId)
{
    // Get the provider based on the provided ID
    $selectedProvider = Provider::findOrFail($providerId);

    $selectedProviderServiceId = Order::where('provider_id', $selectedProvider->id)
        ->where('status', 'completed')
        ->orderBy('created_at', 'desc')
        ->first()
        ->service_id;

    $recommendedProviders = Provider::select('*')
        ->selectSub(function ($query) {
            $query->selectRaw('COUNT(*)')
                  ->from('orders')
                  ->whereColumn('providers.id', 'orders.provider_id')
                  ->where('orders.status', 'completed');
        }, 'total_orders_count')
        ->withAvg('reviews', 'rate')
        ->where('id', '!=', $selectedProvider->id)
        ->where('rate', '>=', $selectedProvider->rate)
        ->whereHas('orders', function ($query) use ($selectedProviderServiceId) {
            $query->where('status', 'completed')
                  ->where('service_id', $selectedProviderServiceId);
        })
        ->orderBy('rate', 'desc')
        ->orderBy('total_orders_count', 'desc')
        ->limit(5)
        ->get();

    // If fewer than 5 providers are found, include lower-rate providers
    if ($recommendedProviders->count() < 5) {
        $recommendedProviderIds = $recommendedProviders->pluck('id')->all();
    
        $lowerRateProviders = Provider::select('*')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(*)')
                      ->from('orders')
                      ->whereColumn('providers.id', 'orders.provider_id')
                      ->where('orders.status', 'completed');
            }, 'total_orders_count')
            ->withAvg('reviews', 'rate')
            ->where('id', '!=', $selectedProvider->id)
            ->whereHas('orders', function ($query) use ($selectedProviderServiceId) {
                $query->where('status', 'completed')
                      ->where('service_id', $selectedProviderServiceId);
            })
            ->whereNotIn('id', $recommendedProviderIds);
    
        // Check if the selected provider's rate is higher than the rest
        if ($selectedProvider->rate > $recommendedProviders->max('rate')) {
            $lowerRateProviders->orderBy('total_orders_count', 'desc')
                ->orderBy('rate', 'asc')
                ->limit(5 - $recommendedProviders->count());
        } else {
            $lowerRateProviders->orderBy('rate', 'desc') // Order by rate in descending order if selected provider's rate is not the highest
                ->limit(5 - $recommendedProviders->count());
        }
    
        $lowerRateProviders = $lowerRateProviders->get();
    
        $recommendedProviders = $recommendedProviders->concat($lowerRateProviders);
    }
    $recommendedProviders->transform(function ($provider) {
        $provider->rate = number_format($provider->rate, 1);
        return $provider;
    });

    return response()->json(['recommended_providers' => $recommendedProviders]);
}
}
    
