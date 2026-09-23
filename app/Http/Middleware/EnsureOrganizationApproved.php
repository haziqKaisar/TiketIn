<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super admin tidak terikat organisasi mana pun, selalu lolos.
        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $organization = $user->organization;

        if (! $organization || ! $organization->isApproved()) {
            return redirect()->route('organizations.pending');
        }

        return $next($request);
    }
}
