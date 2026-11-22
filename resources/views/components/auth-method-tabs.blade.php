@props([
    'active' => 'otp',
    'panel' => 'customer',
])

@php
    $otpRoute = $panel === 'staff' ? route('login.otp.form') : route('customer.login.otp.form');
    $passwordRoute = $panel === 'staff' ? route('login') : route('customer.login');
    $label = $panel === 'staff' ? 'روش ورود کارکنان' : 'روش ورود مشتری';
@endphp

<nav class="auth-method-tabs" aria-label="{{ $label }}">
    <a
        href="{{ $otpRoute }}"
        class="auth-method-tab {{ $active === 'otp' ? 'auth-method-tab-active' : '' }}"
        @if ($active === 'otp') aria-current="page" @endif
    >
        رمز یک‌بارمصرف
    </a>
    <a
        href="{{ $passwordRoute }}"
        class="auth-method-tab {{ $active === 'password' ? 'auth-method-tab-active' : '' }}"
        @if ($active === 'password') aria-current="page" @endif
    >
        نام کاربری و رمز
    </a>
</nav>
