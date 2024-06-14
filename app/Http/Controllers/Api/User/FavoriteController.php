<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\User;
use App\Models\favorites;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware(["auth:user-api"]);
    }
    public function ShowFavorite()
    {
        //$user = auth('user-api')->user();
        // $favorite = auth('user-api')->user()->provider_favorites;
        // return auth('user-api')->user()->favorites->load('provider_favorites');
        return favorites::where('user_id',auth('user-api')->user()->id)->with('provider_favorites')->get();
        return response([
       //     "providers"=> $favorite
        ]);
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
            return response()->json([
                "message"=>"add to favorite"
            ]);
        }
    }
}
