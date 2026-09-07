<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The device agent authenticates with its enroll_token, sent as a
 * bearer token or `token` field. Resolves the Device and attaches it
 * to the request as `agent_device`.
 */
class AuthenticateAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?: $request->input('token');

        if (! $token) {
            return response()->json(['error' => 'missing token'], 401);
        }

        $device = Device::where('enroll_token', $token)->first();

        if (! $device) {
            return response()->json(['error' => 'invalid token'], 401);
        }

        // heartbeat
        $device->update(['last_seen' => now(), 'online' => true]);
        $request->attributes->set('agent_device', $device);

        return $next($request);
    }
}
