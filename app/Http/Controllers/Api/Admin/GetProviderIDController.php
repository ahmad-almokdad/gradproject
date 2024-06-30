<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Provider;
use App\Models\Report;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GetProviderIDController extends Controller
{
    use GeneralTrait;

    public function GetProvider_ByID(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                // 'provider_id' => 'required|string',        
            ]
        );

        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }

        $provider = Provider::selection()->find($request->id);

        if (!$provider) {
            return $this->returnError('001', 'This id is not found');
        }

        $reports = Report::where('provider_id', $provider->id)->get();

        $ordersCompleted = Order::where('provider_id', $provider->id)
            ->where('status', 'completed')
            ->count();

        $pendingOrders = Order::where('provider_id', $provider->id)
            ->where('status', 'pending')
            ->count();

        $processingOrders = Order::where('provider_id', $provider->id)
            ->where('status', 'processing')
            ->count();

        $rejectedOrders = Order::where('provider_id', $provider->id)
            ->where('status', 'rejected')
            ->count();

        return $this->returnData('provider', [
            'info' => $provider,
            'reports' => $reports,
            'total_orders_completed' => $ordersCompleted,
            'pending_orders' => $pendingOrders,
            'processing_orders' => $processingOrders,
            'rejected_orders' => $rejectedOrders,
        ]);
    }

    public function getOrders()
    {
        // Retrieve all orders
        $orders = Order::all();

        // Return the orders as a response
        return response()->json([
            'status' => 200,
            'orders' => $orders,
        ]);
    }

    public function getReports()
    {
        // Retrieve all orders
        $orders = Report::all();

        // Return the orders as a response
        return response()->json([
            'status' => 200,
            'orders' => $orders,
        ]);
    }
}

