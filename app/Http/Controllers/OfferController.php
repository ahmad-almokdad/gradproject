<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Order;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function indexForUser(Request $request)
    {
        $user= auth('user-api')->user();
        $offers = Offer::where('order_id',$request->order_id)->where('user_id',$user->id)->with('provider')->with('order')->with('service')->get();
        return response()->json([
            'status' => true,
            'data' => $offers
        ]);
    }
//    public function indexForProvider()
//    {
//        $provider = auth('provider')->user();
//        $offers = Offer::where('provider_id',null)->whereIn('service_id',$provider->services->pluck('id'))->with('user')->with('order')->with('service')->get();
//        return response()->json([
//            'status' => true,
//            'data' => $offers
//        ]);
//    }
    public function store(Request $request)
    {
        $user = auth('provider')->user();

        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'not found order',
            ]);
        }
        if($order->status != 'pending' ){
            return response()->json([
                'status' => false,
                'message' => 'you can not add price to this order because it not pending status',
            ]);
        }
        Offer::create([
            'total_amount' => $request->price,
            'order_id' => $request->order_id,
            'provider_id' => $user->id,
            'user_id' => $order->user_id,
            'service_id' => $order->service_id,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'order price updated successfully',
            'data' => $order,
        ]);
    }

    public function approveOffer(Request $request)
    {

        $user = auth('user-api')->user();
        $offer = Offer::find($request->offer_id);
        if(!$offer){
            return response()->json([
                'status' => false,
                'message' => 'not found offer',
            ]);
        }
        if($offer->user_id != $user->id){
            return response()->json([
                'status' => false,
                'message' => 'you can not approve this offer',
            ]);
        }
        $order = Order::find($offer->order_id);
        $order->total_amount = $offer->total_amount;
        $order->provider_id = $offer->provider_id;
        $order->approve_status = 'waiting';
        $order->save();
        Offer::where('order_id',$offer->order_id)->delete();
        return response()->json([
            'status' => true,
            'message' => 'offer approved successfully',
            'data' => $offer,
        ]);
    }

}
