<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\ResponseController;

class IsVerified
{
    use ResponseController;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if(!$user instanceof User)
            return $this->response([], 'Something went wrong, trying to retrieve the user !', 404);


        if(!$user->phone_verified_at || !$user->is_active)
            return $this->response([], 'User inactive or phone not verified !', 401);

        return $next($request);
    }
}
