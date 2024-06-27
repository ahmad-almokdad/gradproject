<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\OrderTransaction;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProviderController extends Controller
{


    public function getProfile()
    {
        $provider = auth('provider')->user();
        $total_amount_of_completed = OrderTransaction::where('provider_id', $provider->id)
            ->where('order_status', 'completed')
            ->sum('amount');
        $total_amount_of_pending = OrderTransaction::where('provider_id', $provider->id)
            ->where('order_status', 'pending')
            ->sum('amount');
        $total_amount_earned =$total_amount_of_completed * 0.85;
        $total_amount_of_taken = OrderTransaction::where('provider_id', $provider->id)
            ->where('is_taken',true)
            ->sum('amount');

        $provider->total_amount_of_completed = $total_amount_of_completed;
        $provider->total_amount_of_pending = $total_amount_of_pending;
        $provider->total_amount_earned = $total_amount_earned;
        $provider->total_amount_of_taken = $total_amount_of_taken * 0.85;

        $provider->number_of_complete_order = OrderTransaction::where('provider_id', $provider->id)
            ->where('order_status', 'completed')
            ->count();

        return response()->json([
            'status' => 200,
            'message' => 'Provider fetched successfully',
            'data' => $provider,
        ]);
    }

    public function index(Request $request)
    {
        // if request has service_id get provider by service id else return all
        if ($request->service_id) {
            //i have services relation in Provider model
            // $providers = Provider::where('service_id', $request->service_id)->get();
            $providers = Provider::whereHas('services', function ($query) use ($request) {
                $query->where('service_id', $request->service_id);
            })->get();
        } else {
            //get all with services
            $providers = Provider::with('services')->get();
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


        $token = Auth::guard('provider')->attempt([
            'phone' => $request->phone,
            'password' => $request->password,
        ]);

        if (!$token)
            return $this->returnError('E001', 'بيانات الدخول غير صحيحة');

        return response()->json([
            'status' => 200,
            'message' => 'Provider logged in successfully',
            'data' => $provider,
            'token' => $token,
        ]);
    }
}
