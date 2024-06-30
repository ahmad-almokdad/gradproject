<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Provider;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class GetAllUserController extends Controller
{
    use GeneralTrait;

    public function GetAllUsers(Request $request)
    {
        $users = User::all();

        foreach ($users as $user) {
            $ordersPlaced = Order::where('user_id', $user->id)->count();
            $ordersPending = Order::where('user_id', $user->id)->where('status', 'pending')->count();
            $ordersProcessing = Order::where('user_id', $user->id)->where('status', 'processing')->count();
            $ordersComplete = Order::where('user_id', $user->id)->where('status', 'completed')->count();
            $ordersRejected = Order::where('user_id', $user->id)->where('status', 'rejected')->count();

            $user->orders_placed = $ordersPlaced;
            $user->orders_Pending = $ordersPending;
            $user->orders_Processing = $ordersProcessing;
            $user->orders_Complete = $ordersComplete;
            $user->orders_Rejected = $ordersRejected;
        }

        return $this->returnData('users', $users);
    }
}



