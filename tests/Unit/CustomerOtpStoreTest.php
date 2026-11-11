<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\CustomerOtpStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOtpStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_accepts_the_issued_code_once(): void
    {
        $store = new CustomerOtpStore;
        $plain = $store->issue('09120000000');

        $this->assertTrue($store->verify('09120000000', $plain));
        $this->assertFalse($store->verify('09120000000', $plain));
    }

    public function test_too_many_failed_attempts_forget_the_code(): void
    {
        $store = new CustomerOtpStore;
        $store->issue('09120000000');

        foreach (range(1, CustomerOtpStore::MAX_ATTEMPTS) as $attempt) {
            $this->assertFalse($store->verify('09120000000', '000000'));
        }

        $this->assertFalse($store->verify('09120000000', '000000'));
    }
}
