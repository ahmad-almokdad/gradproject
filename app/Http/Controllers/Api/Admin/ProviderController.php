<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderTransaction;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderController extends Controller
{

    public function getProviders()
    {
        $providers = Provider::all();
        foreach ($providers as $provider) {
            $total_amount_of_completed = OrderTransaction::where('provider_id', $provider->id)
                ->where('order_status', 'completed')
                ->sum('amount');
            $total_amount_of_pending = OrderTransaction::where('provider_id', $provider->id)
                ->where('order_status', 'pending')
                ->sum('amount');
            $amount_earned = $total_amount_of_completed * 0.85;

            $provider->total_amount_of_completed = $total_amount_of_completed;
            $provider->total_amount_of_pending = $total_amount_of_pending;
            $provider->amount_earned = $amount_earned;
        }

        return response()->json([
            'status' => 200,
            'message' => 'Providers fetched successfully',
            'data' => $providers,
        ]);
    }
    public function giveMoneyToProvider(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'provider_id' => 'required',

            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }
        OrderTransaction::where('provider_id',$request->provider_id)
            ->where('order_status','completed')
            ->update(['is_taken'=>true]);

        return response()->json([
            'status' => 200,
            'message' => 'successfully',
        ]);
    }

    public function getStatistic()
    {
        $total_amount_of_completed = OrderTransaction::where('order_status', 'completed')
            ->sum('amount');
        $total_amount_of_pending = OrderTransaction::where('order_status', 'pending')
            ->sum('amount');
        $amount_earned = $total_amount_of_completed * 0.15;

        return response()->json([
            'status' => 200,
            'message' => 'Statistic fetched successfully',
            'data' => [
                'total_amount_of_completed' => $total_amount_of_completed,
                'total_amount_of_pending' => $total_amount_of_pending,
                'amount_earned' => $amount_earned
            ]
        ]);
    }

    public function add_provider(Request $request)
    {
        //add validation
        $validate = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|numeric',
                'address' => 'required|string',
                'password' => 'required|string',
                'service_ids' => 'required|array',
                'service_ids.*' => 'required|numeric'

            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }
        //add provider
        $provider = Provider::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => bcrypt($request->password),
            'status' => 1,
        ]);
        $serviceIds = $request->service_ids;
        $provider->services()->sync($serviceIds);
        return response()->json([
            'status' => 200,
            'message' => 'Provider added successfully',
        ]);
    }

    public function changeActiveProvider(Request $request)
    {

        //validator
        $validate = Validator::make(
            $request->all(),
            [
                'id' => 'required|numeric',
                'status' => 'required|numeric',
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }
        //change provider status
        $provider = Provider::find($request->id);
        $provider->status = $request->status;
        $provider->save();
        return response()->json([
            'status' => 200,
            'message' => 'Provider status changed successfully',
        ]);
    }

    public function assignServiceToProvider(Request $request)
    {
        //validator
        $validate = Validator::make(
            $request->all(),
            [
                'provider_id' => 'required|numeric',
                'service_ids' => 'required|array',
                'service_ids.*' => 'required|numeric'
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }
        //assign service to provider
        $provider = Provider::find($request->provider_id);
        $serviceIds = $request->service_ids;
        $provider->services()->sync($serviceIds);
        return response()->json([
            'status' => 200,
            'message' => 'Service assigned to provider successfully',
        ]);
    }
}
