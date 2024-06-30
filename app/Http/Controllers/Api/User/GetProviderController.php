<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Provider;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GetProviderController extends Controller
{
    use GeneralTrait;


    public function GetProvider_ByID(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
          //      'provider_id' => 'required|string',        
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors(),
            ]);
            
        }
        $provider = Provider::selection()->find($request -> id);
        if(!$provider)
        {
            return $this->returnError('001', 'this id is not found');
        }
        $ordersCompleted = Order::where('provider_id', $provider->id)
            ->where('status', 'completed')
            ->count();

      //  return $this->returnData('providers',$provider);
      return $this->returnData('data', [
        'provider' => $provider,
        'total_orders_completed' => $ordersCompleted,
    ]);
     
    }
    
}
