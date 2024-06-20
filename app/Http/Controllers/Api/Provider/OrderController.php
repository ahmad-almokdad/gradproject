<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function indexByStatus(Request $request)
    {
        $provider = auth('provider')->user();
        if ($request->has('status')) {

             $orders = $provider->orders()->where('status', $request->status)->with('user')->orderBy('id', 'desc')->get();
//            $orders = $provider->orders->where('status', $request->status)->orderBy('id', 'desc')->get();
        } else {
            $orders = $provider->orders()->with('user')->orderBy('id', 'desc')->get();
//            $orders = $provider->orders->orderBy('id', 'desc')->get();
        }
        return response()->json([
            'data' => $orders,
            'status' => true,
        ]);
    }
    public function addPriceToOrder(Request $request)
    {
        $user = auth()->user();


        $order = $user->orders->where('id', $request->order_id)->first();
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'not found order',
            ]);
        }
        $order->total_amount = $request->price;
        $order->approve_status = 'waiting';
        $order->save();
        return response()->json([
            'status' => true,
            'message' => 'order price updated successfully',
            'data' => $order,
        ]);
    }

    public function makeOrderComplete(Request $request)
    {
        $user = auth()->user();
        $order = $user->orders()->where('id', $request->order_id)->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'not found order',
            ]);
        }

        $order->status = 'completed';
        $order->save();
        return response()->json([
            'status' => true,
            'message' => 'order completed successfully',
        ]);
    }

}
