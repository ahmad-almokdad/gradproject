<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\User;
use App\Models\favorites;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    use GeneralTrait;
    public function __construct()
    {
        $this->middleware(["auth:user-api"]);
    }
    public function showFavorite()
{
    $userId = auth('user-api')->user()->id;

    // Retrieve the user's favorites, excluding disabled providers
    $favorites = favorites::where('user_id', $userId)
        ->whereHas('provider_favorites', function ($query) {
            $query->where('status', '1');
        })
        ->with('provider_favorites')
        ->get();

        $favoriteProviders = $favorites->map(function ($favorite) {
            $provider = $favorite->provider_favorites;
            $serviceNames = $provider->services->pluck('service_name');
            $ordersCount = $provider->orders->where('status', 'completed')->count();
    
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
                'order_count' => $ordersCount,
                'service_names' => $serviceNames,
            ];
        });

    return response()->json(['favorite_providers' => $favoriteProviders ]);
}

    public function AddOrRemoveFavorite(Request $request): \Illuminate\Http\JsonResponse
    {
    //    $validator = Validator::make($request->all(),[
    //        'provider_id'=>['required',Rule::exists("providers","id")],
    //        'user_id' => ["required|string"]
  //      ]);
        $validate = Validator::make(
            $request->all(),
            [
                'provider_id' => 'required|string',
                'user_id' => 'required|string'
            ]
        );
        
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }
   //    if ($validator->fails()) {
    //    return response()->json([$validator->errors()]);
  // }
        $favorite = favorites::where([
            'user_id' => $request->user_id,
            'provider_id' => $request->provider_id
        ])->first();
        if(!is_null($favorite)){
            $favorite->delete();
            Provider::where('id', $request->provider_id)->update(['isfavorite' => false]);
            return response()->json([
                "message"=>"delete favorite"
            ]);
        }
        else
        {
            favorites::create([
                'user_id'=> $request->user_id,
                'provider_id'=>$request->provider_id
            ]);
            Provider::where('id', $request->provider_id)->update(['isfavorite' => true]);

            return response()->json([
                "message"=>"add to favorite"
            ]);
        }
    }
    
}
/*
public function showFavorite()
{
    $userId = auth('user-api')->user()->id;

    // Retrieve the user's favorites, excluding disabled providers
    $favorites = favorites::where('user_id', $userId)
        ->whereHas('provider_favorites', function ($query) {
            $query->where('status', '1');
        })
        ->with('provider_favorites')
        ->get();

    return response()->json(['favorite_providers' => $favorites]);
}
    */
