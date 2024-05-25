<?php

namespace App\Http\Middleware;

use App\Traits\GeneralTrait;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckAdminToken
{
    use GeneralTrait;

   //  * Handle an incoming request.
  //   *
 //    * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next

    public function handle($request, Closure $next)
    {
        $user = null;
        try {
            $user = JWTAuth::parseToken()->authenticate();
                //throw an exception
            
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
              //  return response() -> json(['success' => false, 'msg' => 'INVALID _TOKEN']);
                return $this-> returnError('E3001','INVALID _TOKEN');

            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
              //  return response() -> json(['success' =>false, 'msg'=>'EXPIRED_TOKEN']);
              return $this-> returnError('E3001','EXPIRED _TOKEN');
            } else{
                //return response() -> json(['success' => false, 'msg' => 'TOKEN_NOTFOUND']);
                return $this-> returnError('E3001','TOKEN NOTFOUND');
            }
        }

            catch (Throwable $e) {
                if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                    return $this-> returnError('E3001','INVALID _TOKEN');
                }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                    return $this-> returnError('E3001','EXPIRED _TOKEN');
                } else{
                    return $this-> returnError('E3001','TOKEN NOTFOUND');
                }
        }

    if (!$user)
      // return response()->json(['success' => false, 'msg' > trans('Unauthenciated')], 200);
    $this -> returnError(trans('Unauthenciated'), 200);

    return $next($request);

    }
}
