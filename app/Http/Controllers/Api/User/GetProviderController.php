<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Provider;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GetProviderController extends Controller
{
    use GeneralTrait;


    public function GetProvider_ByID(Request $request, $id)
    {
        $validate = Validator::make(
            $request->all(),
            [
          //      'provider_id' => 'required|string',
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);

        }
       // $provider = Provider::selection()->find($request -> id);
        //if(!$provider)
        //{
        //    return $this->returnError('001', 'this id is not found');
        //}
        $provider = Provider::where('id', $id)->where('status', '1')->first();

    if (!$provider) {
        return response()->json(['error' => 'Provider not found or disabled'], 404);
    }
        $ordersCompleted = Order::where('provider_id', $provider->id)
            ->where('status', 'completed')
            ->count();

      //  return $this->returnData('providers',$provider);
      return $this->returnData('data', [
        'provider' => $provider,
        'total_orders_completed' => $ordersCompleted,
    ]);

    }
    public function getProvidersForUser(Request $request)
    {
        $providersQuery = Provider::where('status', '1');

        if ($request->service_id) {
            $providersQuery->whereHas('services', function ($query) use ($request) {
                $query->where('id', $request->service_id);
            });
        }

        $providers = $providersQuery->get();

// Get service names for each provider
        $providerData = $providers->map(function ($provider) {
            $serviceNames = $provider->services->pluck('service_name')->implode(', ');

            return [
                'id' => $provider->id,
                'name' => $provider->name,
                'phone' => $provider->phone,
                'email' => $provider->email,
                'address' => $provider->address,
                'status' => $provider->status,
                'isfavorite' => $provider->isfavorite,
                'rate' => $provider->rate,
                'created_at' => $provider->created_at,
                'updated_at' => $provider->updated_at,
                'order_count' => $provider->orders()->where('status', 'completed')->count(),
                'service_names' => $serviceNames,
            ];
        });

// Return only active providers with service names
        return response()->json(['providers' => $providerData]);
    }

}

/*
public function getProvidersForUser(Request $request)
{
    if($request->service_id){
        $providers = Provider::where('status', '1')
            ->whereHas('services', function($query) use ($request) {
                $query->where('service_id', $request->service_id);
            })
            ->get();
    }else{
        $providers = Provider::where('status', '1')->get();
    }
    // Return only active providers
    return response()->json(['providers' => $providers]);
}
    */

    /*
    // Get service names for each provider
        $providerData = $providers->map(function ($provider) {
            $serviceNames = $provider->services->pluck('service_name')->all();

            return [
                'id' => $provider->id,
                'name' => $provider->name,
                'phone' => $provider->phone,
                'email' => $provider->email,
                'address' => $provider->address,
                'status' => $provider->status,
                'isfavorite' => $provider->isfavorite,
                'rate' => $provider->rate,
                'created_at' => $provider->created_at,
                'updated_at' => $provider->updated_at,
                'order_count' => $provider->orders()->where('status', 'completed')->count(),
                'service_names' => $serviceNames,
            ];
        });
        */
