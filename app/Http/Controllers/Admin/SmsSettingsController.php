<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSmsSettingsRequest;
use App\Services\Sms\FarazSmsClient;
use App\Support\FarazSmsCredentialsStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SmsSettingsController extends Controller
{
    public function edit(FarazSmsCredentialsStore $store, FarazSmsClient $sms): View
    {
        $resolved = $store->resolved();
        $apiKey = $resolved['api_key'];

        return view('admin.sms-settings.edit', [
            'title' => 'SMS settings',
            'patternCode' => $resolved['pattern_code'],
            'otpParam' => $resolved['otp_param'],
            'lineNumber' => $resolved['line_number'],
            'apiKeyConfigured' => $apiKey !== '',
            'apiKeyHint' => $apiKey !== '' ? $this->maskApiKey($apiKey) : null,
            'smsCredit' => $sms->credit(),
        ]);
    }

    public function update(UpdateSmsSettingsRequest $request, FarazSmsCredentialsStore $store): RedirectResponse
    {
        $validated = $request->validated();

        $store->update([
            'api_key' => $validated['api_key'] ?? null,
            'pattern_code' => $validated['pattern_code'] ?? null,
            'otp_param' => $validated['otp_param'] ?? null,
            'line_number' => $validated['line_number'] ?? null,
        ]);

        return redirect()
            ->route('admin.sms-settings.edit')
            ->with('status', 'تنظیمات پیامک ذخیره شد.');
    }

    private function maskApiKey(string $apiKey): string
    {
        $length = strlen($apiKey);

        if ($length <= 8) {
            return str_repeat('•', $length);
        }

        return str_repeat('•', max(0, $length - 4)).substr($apiKey, -4);
    }
}
