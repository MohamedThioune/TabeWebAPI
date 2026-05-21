<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ResponseController;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsVerified
{
    use ResponseController;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->response([], 'Something went wrong, trying to retrieve the user !', 404);
        }

        if (! $user->phone_verified_at || ! $user->is_active) {
            return $this->response([], 'User inactive or phone not verified !', 401);
        }

        return $next($request);
    }
}
