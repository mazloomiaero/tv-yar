<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\AuthBrandingStore;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthBrandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
        Storage::fake('local');
    }

    public function test_admin_can_update_auth_branding_copy(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->put(route('admin.auth-branding.update'), [
                'kicker' => 'برند سازمانی',
                'title' => 'سامانه تست تی‌وی‌یار',
                'lead' => 'توضیح سفارشی برای صفحه ورود.',
                'trust_1' => 'نکته یک',
                'trust_2' => 'نکته دو',
                'trust_3' => 'نکته سه',
            ])
            ->assertRedirect(route('admin.auth-branding.edit'));

        $resolved = app(AuthBrandingStore::class)->resolved();

        $this->assertSame('برند سازمانی', $resolved['kicker']);
        $this->assertSame('سامانه تست تی‌وی‌یار', $resolved['title']);
        $this->assertSame('توضیح سفارشی برای صفحه ورود.', $resolved['lead']);
    }

    public function test_login_page_shows_custom_auth_branding(): void
    {
        app(AuthBrandingStore::class)->update([
            'kicker' => 'برند سازمانی',
            'title' => 'سامانه تست تی‌وی‌یار',
            'lead' => 'توضیح سفارشی برای صفحه ورود.',
            'trust_1' => 'نکته یک',
            'trust_2' => 'نکته دو',
            'trust_3' => 'نکته سه',
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('برند سازمانی')
            ->assertSee('سامانه تست تی‌وی‌یار')
            ->assertSee('توضیح سفارشی برای صفحه ورود.')
            ->assertSee('نکته یک')
            ->assertSee('نکته دو')
            ->assertSee('نکته سه');
    }

    public function test_guest_cannot_update_auth_branding(): void
    {
        $this->put(route('admin.auth-branding.update'), [
            'kicker' => 'برند سازمانی',
            'title' => 'سامانه تست',
            'lead' => 'توضیح',
            'trust_1' => '۱',
            'trust_2' => '۲',
            'trust_3' => '۳',
        ])->assertRedirect();

        $this->assertFalse(Storage::disk('local')->exists(AuthBrandingStore::PATH));
    }
}
