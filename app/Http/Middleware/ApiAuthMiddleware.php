<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      $token = $request->header('Authorization');
      $jwtAuth = new \JwtAuth();
      $checktoken = $jwtAuth->checkToken($token);

      if($checktoken){
        return $next($request);
      }else{
        $data = array(
            'status' =>'Error',
            'code' => 404,
            'message' => 'El usuario no esta identificado'
        );
        return response()->json($data,$data['code']);
      }

    }
}
