<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProviderSearchController extends Controller
{
    use GeneralTrait;

    public function providerSearch(Request $request)
    {
        $name = $request->input('name');
        $serviceId = $request->input('service_id');
        $searchByRate = $request->input('search_by_rate', 0);  // 1 for rate, 0 for not searching by rate
        $searchByOrders = $request->input('search_by_orders', 0);  // 1 for orders, 0 for not searching by orders

    $providers = Provider::where('name', 'like', '%' . $name . '%')
        ->whereHas('services', function ($query) use ($serviceId) {
            $query->where('services.id', $serviceId);
        })
        ->whereHas('orders', function ($query) {
            $query->where('status', 'completed');
        });

    if ($searchByRate == 1 && $searchByOrders == 0) {
        $providers->orderBy('rate', 'desc');
    } elseif ($searchByOrders == 1 && $searchByRate == 0) {
        $providers->withCount(['orders' => function ($query) {
            $query->where('status', 'completed');
        }])->orderBy('orders_count', 'desc');
    } else {
        $providers->orderBy('rate', 'desc');
    }

    $providers = $providers->get();

        if ($providers->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No providers found.',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'providers' => $providers,
        ]);
    }
}