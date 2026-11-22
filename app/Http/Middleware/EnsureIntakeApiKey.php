<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\WebsiteIntakeSettingsStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureIntakeApiKey
{
    public function __construct(private WebsiteIntakeSettingsStore $settings) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $provided = trim((string) $request->header('X-Intake-Key', ''));

        if ($provided === '' || ! $this->settings->apiKeyMatches($provided)) {
            return response()->json([
                'message' => 'Unauthorized intake API key.',
            ], 401);
        }

        if (! $this->settings->isConfigured()) {
            return response()->json([
                'message' => 'Website intake is not fully configured.',
            ], 503);
        }

        return $next($request);
    }
}
