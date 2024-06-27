<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderImage;
use App\Models\OrderTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{

    public function store(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'service_id' => 'required|numeric',
                'provider_id' => 'required|string',
                'schedule_date' => 'required|date',
                'problem_description' => 'required',
                'lat' => 'required|numeric',
                'long' => 'required|numeric',
                'images' => 'required',
                'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }
        $user = auth('user-api')->user();
//        return $request->file('images');
//        dd($request->file('images'));
        $order = Order::create([
            'service_id' => $request->service_id,
            'provider_id' => $request->provider_id,
            'schedule_date' => $request->schedule_date,
            'problem_description' => $request->problem_description,
            'user_id' => $user->id,
            'lat' => $request->lat,
            'long' => $request->long,
        ]);
//        if ($request->hasFile('images')) {

        foreach ($request->file('images') as $image) {
            $imageName = time() . '.'.$image->getClientOriginalName();
            $image->move(public_path('images/orders'), $imageName);
            OrderImage::create([
                'image_url' => $imageName,
                'order_id' => $order->id,
            ]);
        }
//        }
        return response()->json([
            'status' => 200,
            'message' => 'Order Created Successfully',
        ]);
    }

    public function index(Request $request)
    {


        $user = auth('user-api')->user();
        if ($request->has('status')) {
            $orders = $user->orders()->where('status', $request->status)->with('provider')->with('service')->with('images')->orderBy('id', 'desc')->get();
        } else {
            $orders = $user->orders()->with('provider')->with('service')->with('images')->orderBy('id', 'desc')->get();
        }


        // $orders = $user->orders;
        // $orders = $orders->with('service')->with('provider')->get();

//        $orders = $user->load(['orders.service', 'orders.provider']);

        return response()->json([
            'status' => 200,
            'orders' => $orders,
        ]);
    }

    public function approve_order(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'iban' => 'required',
                'order_id' => 'required',
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }
        $user = auth('user-api')->user();
        $order = $user->orders->where('id', $request->order_id)->first();
        if (!$order) {
            return response()->json([
                'status' => 400,
                'message' => 'Order Approved Successfully',
            ], 400);
        }
        $body = [
            'amount' => $order->total_amount,
            'iban' => $request->iban,
        ];
        $response = Http::post('http://localhost:8007/api/request-payment', $body);
        // return $response->body();
        $res_data = json_decode($response->body(), true);
        if ($res_data['error'] != 0) {
            return response()->json([
                'message' => $res_data['message'],
            ], 400);
        }


        OrderTransaction::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'transaction_num' => $res_data['transaction_num'],
            'amount' => $order->total_amount,
            'provider_id' => $order->provider_id,
        ]);
        $order->update([
            'status' => 'processing',
        ]);

        return response()->json([
            'status' => 200,
            'transaction_num' => $res_data['transaction_num'],
            'amount' => $order->total_amount,
            'message' => 'make payment request successfully',
        ]);
    }

    public function approve_payment_order(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'transaction_num' => 'required',
                'otp' => 'required',
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }
        $order_transaction = OrderTransaction::where('transaction_num', $request->transaction_num)->first();
        if (!$order_transaction) {
            return response()->json([
                'status' => 400,
                'message' => 'not found transaction order'
            ], 400);
        }
        $body = [
            'transaction_num' => $request->transaction_num,
            'otp' => $request->otp,
        ];
        $response = Http::post('http://localhost:8007/api/confirm-payment', $body);
        $res_data = json_decode($response->body(), true);
        if ($res_data['error'] != 0) {
            return response()->json([
                'message' => $res_data['message'],
            ], 400);
        }
        $order = Order::where('id', $order_transaction->order_id)->first();
        if (!$order) {
            return response()->json([
                'status' => 400,
                'message' => 'not found  order'
            ], 400);
        }
        $order->payment_status = 'paid';
        $order->approve_status = 'approved';
        $order->save();
        return response()->json([
            'status' => 200,
            'message' => 'order approved and paid successfully',
        ]);
    }

    public function canceledOrder(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'order_id' => 'required',
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }
        $user = auth('user-api')->user();
        $order = $user->orders()->where('id', $request->order_id)->first();
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'not found order',
            ], 404);
        }
        if ($order->status == 'processing' || $order->status == 'completed') {
            return response()->json([
                'status' => false,
                'message' => 'you can not cancel order now',
            ], 403);
        }
        $order->status = 'cancelled';
        $order->cancelled_by = 'user';
        $order->save();
        return response()->json([
            'status' => true,
            'message' => 'order canceled successfully'
        ]);
    }
}
