<?php

namespace App\Http\Middleware;

use App\Models\AccessConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessCode = $request->header("AccessCode");
        if(!$accessCode){
            return response()->json([
                "status" => false,
                "message" => "Unauthorise"
            ], 401);
        }
        $accessConfig = AccessConfig::where("access_code", $accessCode)->where("is_active", 1)->first();
        if(!$accessConfig){
            return response()->json(["status" => false,"message" => "Unauthorise"], 401);
        }
        return $next($request);
    }
}
