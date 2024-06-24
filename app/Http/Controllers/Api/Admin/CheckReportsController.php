<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Traits\AdminTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CheckReportsController extends Controller
{
    public function getReportsforAdmin()
    {
        // Assuming you have an "admin" role or some other way to identify the admin user
      //  if (Auth::user()->isAdmin()) {
            $reports = Report::with('provider')->get();

            return response()->json([
                'status' => 200,
                'reports' => $reports,
            ]);
       // }

       // return response()->json([
       //     'status' => 403,
        //    'message' => 'Unauthorized',
        //], 403);
    }
}
