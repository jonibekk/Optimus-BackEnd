<?php

namespace App\Http\Middleware;

use App\Dto\ResponseDto;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }

    public function handle($request, Closure $next, ...$guards)
    {
        $response = new ResponseDto();

        $token = $request->bearerToken() ?? null;
        
        if($token) {
            $this->authenticate($request, $guards);
        }
        else {
            $response->success = false;
            $response->message = "Unauthorized user!";
            return response($response->toArray(), 401);
        }
        
        return $next($request);
    }
}
