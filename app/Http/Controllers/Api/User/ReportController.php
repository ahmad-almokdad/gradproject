<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
                'provider_id' => 'required|string',
                'report' => 'required|string'
            ]
        );

        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }

    }
}
