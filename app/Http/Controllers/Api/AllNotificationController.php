<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AllNotification;
use Illuminate\Http\Request;

class AllNotificationController extends Controller
{
    public function indexForUser()
    {
        $userId = auth('user-api')->user()->id;
        $notifications = AllNotification::where('user_id',$userId)->get();
        return response()->json(
            [
                "status"=>"success",
                "notifications"=>$notifications
            ]
        );
    }
    public function indexForProvider()
    {
        $user = auth('provider')->user();
        $notifications = AllNotification::where('provider_id',$user->id)->get();
        return response()->json(
            [
                "status"=>"success",
                "notifications"=>$notifications
            ]
        );
    }
}
