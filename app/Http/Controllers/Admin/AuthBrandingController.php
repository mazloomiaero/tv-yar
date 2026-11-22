<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAuthBrandingRequest;
use App\Support\AuthBrandingStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AuthBrandingController extends Controller
{
    public function edit(AuthBrandingStore $store): View
    {
        return view('admin.auth-branding.edit', [
            'title' => 'Login branding',
            'branding' => $store->resolved(),
        ]);
    }

    public function update(UpdateAuthBrandingRequest $request, AuthBrandingStore $store): RedirectResponse
    {
        $store->update($request->validated());

        return redirect()
            ->route('admin.auth-branding.edit')
            ->with('status', 'متن صفحه ورود ذخیره شد.');
    }
}
