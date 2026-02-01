<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * @param $request
     * @param Closure $next
     * @param $role
     * @param null $permission
     * @return mixed
     */
    public function handle($request, Closure $next, $role, $permission = null)
    {
        $check = 0;
        if (stripos($role, '|') !== false) {
            $roles = explode("|", $role);
            foreach ($roles as $role) {
                if (auth()->user()->hasRole($role)) {
                    $check++;
                }
            }
            if ($permission !== null && auth()->user()->can($permission)) {
                $check++;
            }
        }
        else {
            if (auth()->user()->hasRole($role)) {
                $check++;
            }
            if ($permission !== null && auth()->user()->can($permission)) {
                $check++;
            }
        }

        if ($check == 0) {
            abort(404);
        }

//        if(!auth()->user()->hasRole($role)) {
//            abort(404);
//        }
//        if($permission !== null && !auth()->user()->can($permission)) {
//            abort(404);
//        }

        return $next($request);
    }
}
