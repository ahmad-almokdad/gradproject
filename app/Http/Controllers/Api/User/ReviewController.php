<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\User;
use App\Models\Review;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(["auth:user-api"]);
    }

    public function CreateReviewRating (Request $request) {
        try {
        DB::beginTransaction();
        $user = auth()->user();
        $validate = Validator::make(
            $request->all(),
            [
                'provider_id' => 'required',
                'rate' => 'required|numeric|min:1|max:5'
            ]
        );
        
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }
        if($this->CheckCanReview($user,$request->provider_id)===true){
            $review = review::updateOrCreate([
                "provider_id"=>$request->provider_id,
                "user_id"=>$user->id
            ],[
                "provider_id"=>$request->provider_id,
                "user_id"=>$user->id,
                "rate"=>$request->rate
            ]);
            $this->UpdateRateFacility($request->provider_id);
            DB::commit();
            return \response()->json([
                "review" => $review
            ]);
        }else{
            Throw new \Exception("It is not possible");
        }
    }catch (\Exception $exception){
        DB::rollBack();
        return \response()->json([
            "Error" => $exception->getMessage()
        ],401);
            
        }
    }

    private function UpdateRateFacility($id_prov){
        $provider = Provider::where("id",$id_prov)->first();
        $avg = review::where("provider_id",$provider->id)->avg("rate");
        $provider->update([
            "rate" => $avg
        ]);
    }

    private function CheckCanReview($user,$id_prov):bool{
        $temp = $user->providers()->where("provider_id",$id_prov)->first();
        return !is_null($temp);
    }
}
