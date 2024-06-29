<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if ($request->user() == null) {
            return redirect()->route('front.home');
        }
        if ($request->user()->role != 'admin') {
            Session::flash('error', 'You are not authorized to access this page');
            return redirect()->route('front.home');
        }
        return $next($request);
    }
}
