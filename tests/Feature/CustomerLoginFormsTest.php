<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class CustomerLoginFormsTest extends TestCase
{
    public function test_customer_password_login_shows_otp_method_tab(): void
    {
        $this->get(route('customer.login'))
            ->assertOk()
            ->assertSee('رمز یک‌بارمصرف')
            ->assertSee(route('customer.login.otp.form'), false);
    }

    public function test_customer_otp_login_shows_password_method_tab(): void
    {
        $this->get(route('customer.login.otp.form'))
            ->assertOk()
            ->assertSee('نام کاربری و رمز')
            ->assertSee('ارسال کد ورود')
            ->assertSee(route('customer.login'), false);
    }

    public function test_welcome_page_customer_button_goes_to_otp_login(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('customer.login.otp.form'), false);
    }
}
