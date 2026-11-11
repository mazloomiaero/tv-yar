<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;

final class LogoutInternalUser
{
    public function execute(): void
    {
        Auth::guard('web')->logout();
    }
}
