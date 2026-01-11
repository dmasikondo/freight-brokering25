<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckSuspension
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->suspended_at !== null) {
            // Option A: Log them out and redirect with an error
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been suspended. Please contact support.'
            ]);

            // Option B: Keep them logged in but redirect to a 'suspended' info page
            // return redirect()->route('account.suspended');
        }

        return $next($request);
    }
}
