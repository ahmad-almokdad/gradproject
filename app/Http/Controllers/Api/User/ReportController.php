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
                'report' => 'required|string'
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
            $review = $user->reports()->where("reports.provider_id",$request->provider_id)->first();
            return \response()->json([
                "review" => $review
            ]);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
}
