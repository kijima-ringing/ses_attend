<?php

namespace App\Http\Middleware;

use App\Services\GetDateService;
use Closure;
use Illuminate\Support\Facades\Auth;

class LoginUserCheck
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
        $loginId = Auth::id();
        $requestId = $request->user_id;
        $year_month = GetDateService::getNowYearMonth();

        if ($loginId != $requestId) {
            return redirect(route('user.attendance_header.show', ['user_id' => $loginId, 'year_month' => $year_month]));
        }

        return $next($request);
    }
}
