<?php

namespace App\Http\Middleware;

use App\Filament\Pages\PanelSecurity;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrivilegedUserHasTwoFactorAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        if ((! $user->isAdmin()) && (! $user->isSudo())) {
            return $next($request);
        }

        if (! $user->hasVerifiedEmail()) {
            return $next($request);
        }

        if ($request->routeIs(PanelSecurity::getRouteName())) {
            return $next($request);
        }

        if ($user->hasConfirmedTwoFactorAuthentication()) {
            return $next($request);
        }

        return redirect()->to(PanelSecurity::getUrl([
            'enforce2fa' => 1,
        ], isAbsolute: false, panel: 'admin'));
    }
}
