<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendCustomerOtp;
use App\Actions\Auth\VerifyCustomerOtp;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendCustomerOtpRequest;
use App\Http\Requests\Auth\VerifyCustomerOtpRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerOtpLoginController extends Controller
{
    public function create(): View
    {
        return view('auth.customer-otp');
    }

    public function send(SendCustomerOtpRequest $request, SendCustomerOtp $sendCustomerOtp): RedirectResponse
    {
        $mobile = $request->validated('mobile');

        if (! $sendCustomerOtp->execute($mobile)) {
            return redirect()
                ->route('customer.login.otp.form')
                ->withInput()
                ->withErrors(['mobile' => 'این شماره ثبت نمی‌باشد.']);
        }

        $request->session()->put('customer_otp_mobile', $mobile);

        return redirect()
            ->route('customer.login.verify')
            ->with('status', 'کد ورود ارسال شد.');
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('customer_otp_mobile')) {
            return redirect()->route('customer.login.otp.form');
        }

        return view('auth.customer-otp-verify');
    }

    public function verify(VerifyCustomerOtpRequest $request, VerifyCustomerOtp $verifyCustomerOtp): RedirectResponse
    {
        $mobile = $request->session()->get('customer_otp_mobile');

        if (! is_string($mobile) || $mobile === '') {
            return redirect()->route('customer.login.otp.form');
        }

        $verifyCustomerOtp->execute($mobile, $request->validated('otp'));

        $request->session()->forget('customer_otp_mobile');
        $request->session()->regenerate();

        return redirect()->intended(route('customer.dashboard'));
    }
}
