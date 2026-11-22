<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\FarazSmsCredentialsStore;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FarazSmsCredentialsStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config()->set('services.farazsms.api_key', 'env-key');
        config()->set('services.farazsms.pattern_code', 'ENVCODE');
        config()->set('services.farazsms.otp_param', 'token');
        config()->set('services.farazsms.line_number', '5000');
    }

    public function test_resolved_falls_back_to_env_when_store_empty(): void
    {
        $resolved = (new FarazSmsCredentialsStore)->resolved();

        $this->assertSame('env-key', $resolved['api_key']);
        $this->assertSame('ENVCODE', $resolved['pattern_code']);
        $this->assertSame('token', $resolved['otp_param']);
        $this->assertSame('5000', $resolved['line_number']);
    }

    public function test_stored_values_override_env(): void
    {
        $store = new FarazSmsCredentialsStore;
        $store->update([
            'api_key' => 'stored-key',
            'pattern_code' => 'P123',
            'otp_param' => 'code',
            'line_number' => '90008361',
        ]);

        $resolved = $store->resolved();

        $this->assertSame('stored-key', $resolved['api_key']);
        $this->assertSame('P123', $resolved['pattern_code']);
        $this->assertSame('code', $resolved['otp_param']);
        $this->assertSame('90008361', $resolved['line_number']);
        $this->assertTrue(Storage::disk('local')->exists(FarazSmsCredentialsStore::PATH));
    }

    public function test_blank_api_key_keeps_existing_stored_key(): void
    {
        $store = new FarazSmsCredentialsStore;
        $store->update(['api_key' => 'keep-me', 'pattern_code' => 'A']);
        $store->update(['api_key' => '', 'pattern_code' => 'B']);

        $resolved = $store->resolved();

        $this->assertSame('keep-me', $resolved['api_key']);
        $this->assertSame('B', $resolved['pattern_code']);
    }
}
