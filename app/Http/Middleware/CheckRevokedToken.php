<?php

namespace App\Http\Middleware;

use App\Services\TokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRevokedToken
{
    public function __construct(
        private readonly TokenService $tokenService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (! $token) {
            return $next($request);
        }

        $tokenId = $this->tokenService->extractTokenId($token);

        if ($this->tokenService->isTokenRevoked($tokenId)) {
            return response()->json([
                'message' => 'Token has been revoked.',
            ], 401);
        }

        $request->attributes->set('_token_id', $tokenId);

        return $next($request);
    }

    /**
     * Perform any final actions for the request lifecycle.
     */
    public function terminate(Request $request, Response $response): void
    {
        $tokenId = $request->attributes->get('_token_id');

        if (is_string($tokenId) && $tokenId !== '') {
            $this->tokenService->updateTokenLastUsed($tokenId);
        }
    }
}
