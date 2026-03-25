<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $permissionsArray = explode('|', $permission);
        // if(!$user or !$user->hasPermission($permission)) abort(403,"Unauthorized Action");
        $authorized = false;
        foreach($permissionsArray as $perm) {
            if ($user and $user->hasPermission($perm)) {
                $authorized = true;
                break;
            }
        }
        if($authorized == false) abort(403, "Unauthorized Action");

        return $next($request);
    }
}
