<?php

namespace App\Http\Middleware;

use App\Support\BusinessProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessProfileIsComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->is('admin/system-settings*')
            || $request->is('admin/logout')
            || $request->is('livewire/*')
            || ! BusinessProfile::requiresSetup()
        ) {
            return $next($request);
        }

        return redirect(BusinessProfile::setupUrl());
    }
}
