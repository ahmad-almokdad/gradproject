<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function indexByStatus(Request $request)
    {
        $provider = auth('provider')->user();
        if ($request->with_provider == 1) {
            if ($request->has('status')) {

                $orders = $provider->orders()->where('status', $request->status)->with('images')->with('user')->orderBy('id', 'desc')->get();
//            $orders = $provider->orders->where('status', $request->status)->orderBy('id', 'desc')->get();
            } else {
                $orders = $provider->orders()->with('images')->with('user')->orderBy('id', 'desc')->get();
//            $orders = $provider->orders->orderBy('id', 'desc')->get();
            }
        } else {

            $orders = Order::where('provider_id', null)->whereIn('service_id', $provider->services->pluck('id'))->with('user')->with('service')
                ->with('images')
                ->with(['offers' => function ($query) use ($provider) {
                    $query->where('provider_id', $provider->id)->first();
                }])
                ->get();
        }

        return response()->json([
            'data' => $orders,
            'status' => true,
        ]);
    }


    public function addPriceToOrder(Request $request)
    {
        $user = auth('provider')->user();

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
        $user = auth('provider')->user();
        $order = $user->orders()->where('id', $request->order_id)->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'not found order',
            ]);
        }

        $order->status = 'completed';
        $order->save();
        $order_transaction = OrderTransaction::where('order_id', $order->id)->first();
        if (!$order_transaction) {
            return response()->json([
                'message' => 'not found transaction',
            ], 400);
        }
        $order_transaction->order_status = 'completed';
        $order_transaction->save();

        return response()->json([
            'status' => true,
            'message' => 'order completed successfully',
        ]);
    }

    public function canceledOrder(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'order_id' => 'required',
                'reason' => 'required'
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }
        $user = auth('provider')->user();
        $order = $user->orders()->where('id', $request->order_id)->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'not found order',
            ]);
        }

        $order->status = 'cancelled';
        $order->reject_reason = $request->reason;
        $order->cancelled_by = 'provider';
        $order->save();
        return response()->json([
            'status' => true,
            'message' => 'order canceled successfully'
        ]);
    }

}
