<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendStaffOtp;
use App\Actions\Auth\VerifyStaffOtp;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendStaffOtpRequest;
use App\Http\Requests\Auth\VerifyStaffOtpRequest;
use App\Support\StaffHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternalOtpLoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login-otp');
    }

    public function send(SendStaffOtpRequest $request, SendStaffOtp $sendStaffOtp): RedirectResponse
    {
        $mobile = $request->validated('mobile');

        if (! $sendStaffOtp->execute($mobile)) {
            return redirect()
                ->route('login.otp.form')
                ->withInput()
                ->withErrors(['mobile' => 'این شماره ثبت نمی‌باشد.']);
        }

        $request->session()->put('staff_otp_mobile', $mobile);

        return redirect()
            ->route('login.verify')
            ->with('status', 'کد ورود ارسال شد.');
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('staff_otp_mobile')) {
            return redirect()->route('login.otp.form');
        }

        return view('auth.login-otp-verify');
    }

    public function verify(VerifyStaffOtpRequest $request, VerifyStaffOtp $verifyStaffOtp, StaffHome $staffHome): RedirectResponse
    {
        $mobile = $request->session()->get('staff_otp_mobile');

        if (! is_string($mobile) || $mobile === '') {
            return redirect()->route('login.otp.form');
        }

        $user = $verifyStaffOtp->execute($mobile, $request->validated('otp'));

        $request->session()->forget('staff_otp_mobile');
        $request->session()->regenerate();

        return redirect()->intended($staffHome->url($user));
    }
}
