<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\User;
use App\Models\Order;
use App\Models\Review;
use App\Models\ServicesProviders;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
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


    public function CreateReviewRating (Request $request, Order $order) {
        try {
            // $userId = auth('user-api')->user()->id;
            // dd(Auth::id()) ;
            $orderInfo = Order::findOrFail($request->order_id) ;
            // Check if the order has been completed by the provider
        if ($orderInfo->status != 'completed') {
            return response()->json(['error' => 'Service must be completed before submitting a review'], 400);
        }

        // Check if the user has already submitted a review for the order
        if (Review::where('id', $order->id)->where('user_id', Auth::id())->exists()) {
            return response()->json(['error' => 'You have already submitted a review for this service'], 400);
        }
        $user = auth('user-api')->user();
        $validate = Validator::make(
            $request->all(),
            [
                'order_id' => 'required|string',
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
        $checkIfReviewed = Review::where('user_id',$user->id)->where('order_id',$request->order_id)->exists();
        if($checkIfReviewed){
            Throw new \Exception("You Are Already Submit Your Review",code: 400);
        }
        if($this->CheckCanReview($user,$request->order_id)===true){
            $review = review::updateOrCreate([
                "order_id"=>$request->order_id,
                "provider_id"=>$request->provider_id,
                "user_id"=>$user->id
            ],[
                "order_id"=>$request->order_id,
                "provider_id"=>$request->provider_id,
                "user_id"=>$user->id,
                "rate"=>$request->rate
            ]);

            $this->UpdateRateProvider($request->order_id);

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
                    'order_id' => 'required|string',
                  //  'rate' => 'required|numeric|min:1|max:5'
                ]
            );
            if ($validate->fails()) {
                return response()->json([
                    'status' => 400,
                    'errors' => $validate->errors(),
                ]);
            }
            $review = $user->reviews()->where("reviews.order_id",$request->order_id)->first();
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
                    'order_id' => 'required|string',
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
                    ->where("order_id",$request->order_id)->first();
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
            $this->UpdateRateProvider($request->order_id); //fix it
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

    private function UpdateRateProvider($orderId){
       // $provider = Provider::where("id",$id_prov)->first();
       // if(!$provider){
        //    return response()->json(['message'=>'not found'],400);
       // }
       // $avg = review::where("provider_id",$provider->id)->avg("rate");
        //$provider->update([
        //    "rate" => $avg
        //]);
    // Retrieve the order
    $order = Order::findOrFail($orderId);

    // Retrieve the associated provider
    $provider = $order->provider()->first();


    if ($provider) {
        // Calculate the average rating for the provider
        $averageRating = review::where('provider_id', $provider->id)
            // ->where('status', 'completed')
            ->avg('rate');

        // Update the provider's rating
        $provider->rate = $averageRating ?? 0;

        $provider->save();

        // Update the order's rating
        $order->rate = $averageRating ??0 ;
        $order->save();
    } else {
        // Handle the case when the provider is not found
        throw new \Exception('Provider not found.');
    }
}

    public function CheckCanReview(User $user, $orderId)
{
    // Retrieve the order
    $order = Order::findOrFail($orderId);

    // Check if the order belongs to the user
    if ($order->user_id !== $user->id) {
        throw new \Exception("You are not authorized to review this order");
    }

    // Check if the provider has completed the order for the user
    return $order->status =='completed';
}
}
