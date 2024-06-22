<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Provider;
use App\Models\User;
use App\Models\Report;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(["auth:user-api"]);
    }

    public function AddReport(Request $request) {
        $validate = Validator::make(
            $request->all(),
            [
                'order_id' => 'required|string',
                'report' => 'required|string'
            ]
        );

        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }

        $user = auth('user-api')->user();
        $provider = Provider::where("id",$request->provider_id)->first();

    }

    public function CheckCanReport(User $user, $orderId)
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
