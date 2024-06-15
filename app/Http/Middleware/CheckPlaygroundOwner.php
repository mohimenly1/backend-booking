<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Playground;

class CheckPlaygroundOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Retrieve the playground ID from the route
        $playgroundId = $request->route('playground');

        // Retrieve the playground instance
        $playground = Playground::find($playgroundId);

        // Check if the playground exists
        if (!$playground) {
            return response()->json(['error' => 'Playground not found'], 404);
        }

        // Check if the authenticated user is the owner of the playground
        if ($playground->owner_id !== Auth::id()) {
            return response()->json(['error' => 'You do not have permission to update this playground'], 403);
        }

        return $next($request);
    }
}
