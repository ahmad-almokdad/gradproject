<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{

    public function index()
    {
        $services = Service::all();
        return response()->json([
            'status' => 200,
            'services' => $services,
        ]);
    }
    // public function index
    public function addService(Request $request)
    {
        //make validator
        $validate = Validator::make(
            $request->all(),
            [
                'service_name' => 'required|string',
                'service_description' => 'required|string',
                'service_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:8048',
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
        }

        //upload image
        $imageName = time() . '.' . $request->service_image->extension();
        $request->service_image->move(public_path('images/services'), $imageName);

        //insert data
        $service = new Service();
        $service->service_name = $request->service_name;
        $service->service_description = $request->service_description;
        $service->service_image = $imageName;
        $service->save();
        return response()->json([
            'status' => 200,
            'message' => 'Service added successfully',
        ]);
    }
}