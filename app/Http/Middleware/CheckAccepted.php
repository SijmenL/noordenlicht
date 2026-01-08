<?php

namespace App\Http\Middleware;

use App\Models\Log;
use Closure;

class CheckAccepted
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
        $user = \Auth::user();

        // Check if user is NOT logged in OR if allow_booking is not true
        if (!$user || $user->allow_booking != true) {

            // Only attempt to log if a user is actually logged in to prevent a crash
            if ($user) {
                $log = new Log();
                $log->createLog($user->id, 1, 'Bekijk pagina', $request->route()->getName(), '', 'Gebruiker is nog niet geaccepteerd');
            }

            return redirect()->route('home')->with('error', 'Je hebt nog geen toegang tot deze pagina.');
        }

        // If the user has been accepted, proceed with the request
        return $next($request);
    }
}
