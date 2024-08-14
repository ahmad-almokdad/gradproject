<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UAuthController extends Controller
{

    use GeneralTrait;


    public function editProfile(Request $request)
    {
        $user = User::find(Auth::guard('user-api')->user()->id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;

        $user->save();
        return $this->returnData('user', $user);
    }
    public function updateFCMToken(Request $request)
    {
        $user = User::find(Auth::guard('user-api')->user()->id);
        $user->fcm_token = $request->fcm_token;
        $user->save();
    }

    public function changePassword(Request $request)
    {
        $user = User::find(Auth::guard('user-api')->user()->id);
        $user->password = bcrypt($request->password);
        //check old password
        if (!Hash::check($request->old_password, $user->password)) {
            return $this->returnError('E001', 'بيانات الدخول غير صحيحة');
        }
        $user->save();
        return $this->returnData('user', $user);
    }

    public function getProfile()
    {
        $user = User::find(Auth::guard('user-api')->user()->id);
        return $this->returnData('user', $user);
    }
    public function login(Request $request)
    {

        try {
            $rules = [
                // "email" => "required|email",
                "phone" => 'required',
                "password" => "required",


            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $code = $this->returnCodeAccordingToInput($validator);
                return $this->returnValidationError($code, $validator);
            }

            //login

            $credentials = $request->only(['phone', 'password']);

            $token = Auth::guard('user-api')->attempt($credentials);  //generate token

            if (!$token)
                return $this->returnError('E001', 'بيانات الدخول غير صحيحة');

            $user = Auth::guard('user-api')->user();
            $user->api_token = $token;
            //return token
            return $this->returnData('user', $user);  //return json response

        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }


    public function register(Request $request)
    {

        try {
            $rules = [
                "email" => "required|email|unique:users,email",
                "phone" => 'required|unique:users,phone',
                "password" => "required",
                'name' => 'required',

            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $code = $this->returnCodeAccordingToInput($validator);
                return $this->returnValidationError($code, $validator);
            }
            //login
            // $credentials = $request->only(['email', 'password']);
            // $token = Auth::guard('user-api')->attempt($credentials);  //generate token
            // if (!$token)
            //     return $this->returnError('E001', 'بيانات الدخول غير صحيحة');
            // return 'h3';
            // return $request;
            return   $user = User::create([
                "name" => $request->name,
                "email" => $request->email,
                "password" => bcrypt($request->password),
                "phone" => $request->phone,
            ]);
            // $user = Auth::guard('user-api')->user();
            // $user->api_token = $token;
            //return token
            return $this->returnData('user', $user);  //return json response

        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function logout(Request $request)
    {
        $token = $request->header('authorization');
        $token = str_replace('Bearer ', '', $token);

        if ($token) {
            try {

                JWTAuth::setToken($token)->invalidate(); //logout
            } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                return  $this->returnError('', 'some thing went wrongs');
            }
            return $this->returnSuccessMessage('Logged out successfully');
        } else {
            $this->returnError('', 'some thing went wrongs');
        }
    }
}
