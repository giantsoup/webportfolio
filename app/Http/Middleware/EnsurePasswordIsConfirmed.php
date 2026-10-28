<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Preserve password confirmation denials when Livewire replays route middleware.
 */
class EnsurePasswordIsConfirmed
{
    public function __construct(private RequirePassword $requirePassword) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $this->requirePassword->handle($request, $next);

        abort_if($response->getStatusCode() === Response::HTTP_LOCKED, $response);

        return $response;
    }
}
