<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\User;
use App\Models\Review;
use App\Models\ServicesProviders;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Notifications\UserNotification;
use Carbon\Carbon;
use App\Traits\GeneralTrait;



class ReviewController extends Controller
{
    use GeneralTrait;
    public function __construct()
    {
        $this->middleware(["auth:user-api"])->except("ShowReviewAll");
    }

    public function ShowReviewAll(Request $request)
    {

        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'provider_id' => 'required',
                ]
            );

            if ($validate->fails()) {
                return response()->json([
                    'status' => 400,
                    'errors' => $validate->errors(),
                ]);
            }
            $reviews = review::where("reviews.provider_id",$request->provider_id)
                ->orderBy("reviews.id")
                ->paginate($this->NumberOfValues($request));
            $reviews = $this->Paginate("reviews",$reviews);

            foreach ($reviews["reviews"] as $item){
                $temp_user = User::where("id",$item->user_id)->first();
                $item->user = [
                    "name"=> $temp_user->name ?? null,
                ];
            }
            return \response()->json($reviews);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function CreateReviewRating (Request $request) {
        try {
            DB::beginTransaction();
            $user = auth('user-api')->user();
            $validate = Validator::make(
                $request->all(),
                [
                    'provider_id' => 'required|string',
                    'rate' => 'required|numeric|min:1|max:5'
                ]
            );

            if ($validate->fails()) {
                return response()->json([
                    'status' => 400,
                    'errors' => $validate->errors(),
                ]);
            }
            if($this->CheckCanReview($user,$request->provider_id)===false){
                $review = review::updateOrCreate([
                    "provider_id"=>$request->provider_id,
                    "user_id"=>$user->id
                ],[
                    "provider_id"=>$request->provider_id,
                    "user_id"=>$user->id,
                    "rate"=>$request->rate
                ]);
                $this->UpdateRateProvider($request->provider_id);
                DB::commit();
                return \response()->json([
                    "review" => $review
                ]);
            }else{
                Throw new \Exception("It is not possible to rate the provider since he didn't serve you");
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);

        }
    }

    public function GetReview(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();
            $validate = Validator::make(
                $request->all(),
                [
                    'provider_id' => 'required|string',
                    'rate' => 'required|numeric|min:1|max:5'
                ]
            );
            if ($validate->fails()) {
                return response()->json([
                    'status' => 400,
                    'errors' => $validate->errors(),
                ]);
            }
            $review = $user->reviews()->where("reviews.provider_id",$request->provider_id)->first();
            return \response()->json([
                "review" => $review
            ]);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    public function DeleteReview(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $validate = Validator::make(
                $request->all(),
                [
                    'provider_id' => 'required|string',
                    'review_id' => 'required|numeric'
                ]
            );
            if($validate->fails()){
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            $user = auth()->user();
            $rev = null;
            $rev = review::where("id",$request->review_id)
                ->where("provider_id",$request->provider_id)->first();
            if(is_null($rev)){
                Throw new \Exception("The Review is Not Found");
            }
            $rev->delete();
            //  }
            //   else{
            //      $rev = review::where("id",$request->review_id)
            //          ->where("provider_id",$request->provider_id)->where("user_id",$user->id)->first();
            //     if(is_null($rev)){
            //        Throw new \Exception("The Review is Not Found");
            //   }
            //    $rev->delete();
            //   }
            $this->UpdateRateProvider($request->provider_id);
            DB::commit();
            return \response()->json([
                "message" => "Success Delete Reviews"
            ]);
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    private function UpdateRateProvider($id_prov){
        $provider = Provider::where("id",$id_prov)->first();
        if(!$provider){
            return response()->json(['message'=>'not found'],400);
        }
        $avg = review::where("provider_id",$provider->id)->avg("rate");
        $provider->update([
            "rate" => $avg
        ]);
    }

    private function CheckCanReview($user,$id_prov):bool{
        $temp = $user->Providers()->where("id",$id_prov)->first();
        return !is_null($temp);
    }
}
