<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class AddSecurityHeadersTest extends TestCase
{
    public function test_public_responses_include_security_headers(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
        $response->assertHeader('Strict-Transport-Security', 'max-age=15552000; includeSubDomains');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeaderMissing('Content-Security-Policy');
    }
}
