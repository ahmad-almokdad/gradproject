<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
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
                'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }
        $user = auth('user-api')->user();
        $order = Order::create([
            'service_id' => $request->service_id,
            'provider_id' => $request->provider_id,
            'schedule_date' => $request->schedule_date,
            'problem_description' => $request->problem_description,
            'user_id' => $user->id,
            'lat' => $request->lat,
            'long' => $request->long,
        ]);
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/orders'), $imageName);
                $order->images()->create([
                    'image_url' => $imageName,
                ]);
            }
        }
        return response()->json([
            'status' => 200,
            'message' => 'Order Created Successfully',
        ]);
    }
    public function index()
    {
        $user = auth('user-api')->user();
        $orders = $user->orders->with('service')->with('provider')->get();
        return response()->json([
            'status' => 200,
            'orders' => $orders,
        ]);
    }
    public function approve_order(Request $request)
    {
        $user = auth('user-api')->user();
        $order = $user->orders->where('id', $request->order_id)->first();
        $order->approve_status = 'approved';
        $order->save();
        return response()->json([
            'status' => 200,
            'message' => 'Order Approved Successfully',
        ]);

    }
    
}
