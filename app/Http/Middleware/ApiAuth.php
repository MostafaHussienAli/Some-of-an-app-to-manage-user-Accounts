<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class ApiAuth extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next ,...$guards) {

        try {

            $response = $next($request);



            if (isset($response->exception) && $response->exception) {

                throw $response->exception;

            }



            return $response;

        } catch (\Exception $e) {
            if($e->getMessage()=='Unauthenticated.'){
                $status=3;
            }else{
                $status=0;
            }

            return response()->json(array(

                'status' => $status,
                'message' => $e->getMessage(),
                'data'=>null,

            ),200);

        }

    }
}
