<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderController extends Controller
{
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

            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }
        //add provider
        Provider::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => bcrypt($request->password),
            'status' => 1,
        ]);
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
