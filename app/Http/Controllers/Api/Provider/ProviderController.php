<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderController extends Controller
{

    public function index(Request $request)
    {
        // if request has service_id get provider by service id else return alll
        if ($request->service_id) {
            $providers = Provider::where('service_id', $request->service_id)->get();
        } else {
            $providers = Provider::all();
        }
        return response()->json([
            'status' => 200,
            'message' => 'Providers fetched successfully',
            'data' => $providers,
        ]);
    }
    public function login(Request $request)
    {

        //login by phone and password
        $validate = Validator::make(
            $request->all(),
            [
                'phone' => 'required|numeric',
                'password' => 'required|string',
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }

        $provider = Provider::where('phone', $request->phone)->first();
        if (!$provider) {
            return response()->json([
                'status' => 404,
                'message' => 'Provider not found',
            ]);
        }
        if (!password_verify($request->password, $provider->password)) {
            return response()->json([
                'status' => 400,
                'message' => 'Password is incorrect',
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Provider logged in successfully',
            'data' => $provider,
        ]);
    }
}
