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

    public function providerSearch($name)
    {
        $query = Provider::query();

        if (!empty($name)) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        $providers = $query->withCount(['orders' => function ($query) {
                $query->where('status', 'completed'); // Only count orders with status 'completed'
            }])->get();
//        $providers = $query->select('providers.*', DB::raw('COUNT(orders.id) as completed_orders'))
//            ->leftJoin('orders', 'providers.id', '=', 'orders.provider_id')
//            ->where('orders.status', 'completed')
//            ->groupBy('providers.id')
//            ->orderBy('providers.rate', 'desc')
//            ->orderBy('completed_orders', 'desc')
//            ->take(1)
//            ->get();

        if ($providers->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No providers found.',
            ], 404);
        }

        $provider = $providers->first();

        return response()->json([
            'status' => 200,
            'provider' => $provider,
        ]);
    }
}
