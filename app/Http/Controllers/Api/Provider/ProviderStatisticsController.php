<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderStatistics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ProviderStatisticsController extends Controller
{


    public function getProviderStatistics($providerId)
{
    $provider = Provider::find($providerId);

    if (!$provider) {
        return response()->json(['error' => 'Provider not found'], 404);
    }

    $statistics = ProviderStatistics::where('provider_id', $providerId)->get();

    return response()->json(['statistics' => $statistics]);
}
public function calculateProviderStatistics()
{
    $providers = Provider::all();

    foreach ($providers as $provider) {
        // Calculate the profit for the provider based on relevant data (e.g., transactions, orders)
        // Custom profit calculation logic example
        $totalRevenue = $provider->orders()->sum('total_amount');
        $totalCOGS = $provider->orders()->sum('total_cogs');
        $totalExpenses = $provider->expenses()->sum('amount');

        // Calculate profit as total revenue minus COGS and expenses
        $profit = $totalRevenue - $totalCOGS - $totalExpenses;

        // Get the current month and year
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');

        // Check if the statistics already exist for the current month and year
        $statistics = ProviderStatistics::where('provider_id', $provider->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        // If statistics exist, update the profit; otherwise, create new statistics
        if ($statistics) {
            $statistics->profit = $profit;
            $statistics->save();
        } else {
            ProviderStatistics::create([
                'provider_id' => $provider->id,
                'month' => $currentMonth,
                'year' => $currentYear,
                'profit' => $profit,
            ]);
        }
    }
}
}