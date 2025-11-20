<?php

namespace App\Http\Middleware;

use App\Services\TokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRevokedToken
{
    /**
     * Initialize the middleware.
     *
     * Constructs the middleware with injected token service dependency.
     *
     * @param  TokenService  $tokenService  Service for token validation and tracking
     */
    public function __construct(
        private readonly TokenService $tokenService
    ) {}

    /**
     * Handle an incoming request.
     *
     * Checks if the bearer token has been revoked and rejects the request if so.
     * Stores the token ID in request attributes for later use in terminate method.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure(Request): (Response)  $next  The next middleware closure
     * @return Response The HTTP response
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
     *
     * Updates the last used timestamp for the token in Redis if a valid token was present.
     *
     * @param  Request  $request  The HTTP request
     * @param  Response  $response  The HTTP response
     */
    public function terminate(Request $request, Response $response): void
    {
        $tokenId = $request->attributes->get('_token_id');

        if (is_string($tokenId) && $tokenId !== '') {
            $this->tokenService->updateTokenLastUsed($tokenId);
        }
    }
}
