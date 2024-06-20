<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GetUserController extends Controller
{
    use GeneralTrait;


    public function GetUser_ByID(Request $request)
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
        $user = User::selection()->find($request -> id);
        if(!$user)
        {
            return $this->returnError('001', 'this id is not found');
        }

        return $this->returnData('users',$user);
     //   $provider = Provider::selection()->find($request -> id);
     //   if(!$provider)
    //    {
     //     return $this->returnError('001', 'this id is not found');
     //   }

      //  return Provider::where('id')->with('getProvider_byid')->get();
    }
   
}