<?php

namespace App\Http\Middleware;

use App\Enums\BusinessStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the seller areas. A seller needs the seller role and an approved
 * business, because a pending business cannot fulfil orders yet.
 */
class EnsureSeller
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isSeller()) {
            abort(403, 'This area is for sellers.');
        }

        $business = $user->sellerBusiness();

        if ($business === null) {
            return redirect()->route('seller.onboarding');
        }

        if ($business->status === BusinessStatus::Pending) {
            return redirect()->route('seller.pending');
        }

        // Suspended shops keep their data but lose access to the seller desk.
        if ($business->status !== BusinessStatus::Active) {
            abort(403, 'This shop is not active.');
        }

        return $next($request);
    }
}
