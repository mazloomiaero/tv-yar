<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class PanelRouteStubTest extends TestCase
{
    public function test_guest_is_redirected_from_staff_panels_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/operator')->assertRedirect(route('login'));
        $this->get('/technical')->assertRedirect(route('login'));
        $this->get('/accountant')->assertRedirect(route('login'));
        $this->get('/technician')->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_customer_panel_to_customer_login(): void
    {
        $this->get('/customer')->assertRedirect(route('customer.login'));
    }
}
