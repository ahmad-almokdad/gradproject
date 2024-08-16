<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Provider;
use App\Models\User;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
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
                'provider_id' => 'required|exists:providers,id',
                'report' => 'required|string',
//                'order_id' => 'required'
            ]
        );

        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }

        $providerId = $request->input('provider_id');
        $reportText = $request->input('report');
        $userId = Auth::id();

    try {
        $provider = Provider::findOrFail($providerId);

        $report = new Report();
        $report->report = $reportText;
        $report->user_id = $userId;
        $report->order_id = $request->order_id;
        if($request->order_id){
            $order = Order::where('id',$request->order_id)->where('user_id',$userId)->first();
            if(!$order){
                return response()->json([
                    "message"=>"not found order "
                ],400);
            }
            $order->status = "processing";
            $order->report_status = 1;
            $order->save();
        }



        $provider->reports()->save($report);

        return response()->json([
            'status' => 200,
            'message' => 'Report submitted successfully',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'An error occurred while submitting the report',
            'error' => $e->getMessage(), // Add the exception message
        ], 500);
    }
}
public function GetReport(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();
            $validate = Validator::make(
                $request->all(),
                [
                    'provider_id' => 'required|string',
                ]
            );
            if ($validate->fails()) {
                return response()->json([
                    'status' => 400,
                    'errors' => $validate->errors(),
                ]);
            }
            $report = $user->reports()->where("reports.provider_id",$request->provider_id)->first();
            return \response()->json([
                "report" => $report
            ]);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
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
