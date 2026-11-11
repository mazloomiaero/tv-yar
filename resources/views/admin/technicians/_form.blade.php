@php
    $passwordRequired = $technician === null || $technician->user_id === null;
    $mobileValue = old('mobile', $technician?->user?->mobile ?: $technician?->user?->username);
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-field-label for="first_name" :required="true">نام</x-field-label>
        <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $technician?->givenName() ?? '') }}" maxlength="100" required class="ui-input">
        @error('first_name')
            <p class="mt-1 text-sm" style="color:#dc2626">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <x-field-label for="last_name" :required="true">نام خانوادگی</x-field-label>
        <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $technician?->familyName() ?? '') }}" maxlength="100" required class="ui-input">
        @error('last_name')
            <p class="mt-1 text-sm" style="color:#dc2626">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-field-label for="mobile" :required="true">شماره همراه</x-field-label>
        <input
            id="mobile"
            type="text"
            name="mobile"
            value="{{ $mobileValue }}"
            maxlength="11"
            inputmode="numeric"
            required
            class="ui-input {{ $errors->has('mobile') ? 'border-red-500' : '' }}"
            autocomplete="tel"
        >
        <p class="mt-1 text-xs text-slate-500">همین شماره، نام کاربری ورود به پنل تکنسین است.</p>
        <p id="mobile-error" class="mt-1 text-sm" style="color:#dc2626" @if (! $errors->has('mobile')) hidden @endif>
            {{ $errors->first('mobile') ?: 'شماره همراه باید با ۰۹ شروع شود و ۱۱ رقم باشد.' }}
        </p>
    </div>
    <div>
        <x-field-label for="branch_id" :required="true">شعبه</x-field-label>
        <select id="branch_id" name="branch_id" required class="ui-select">
            <option value="">انتخاب شعبه</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id', $technician?->branch_id) === (string) $branch->id)>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @error('branch_id')
            <p class="mt-1 text-sm" style="color:#dc2626">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-field-label for="password" :required="$passwordRequired">رمز عبور</x-field-label>
        <div style="position: relative;">
            <input
                id="password"
                type="password"
                name="password"
                class="ui-input"
                style="padding-inline-end: 2.75rem;"
                autocomplete="new-password"
                @required($passwordRequired)
            >
            <button
                type="button"
                data-password-toggle
                aria-label="نمایش رمز عبور"
                style="position:absolute;inset-inline-end:0;top:0;bottom:0;width:2.75rem;border:0;background:transparent;cursor:pointer;color:#475569;"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            </button>
        </div>
        @if ($technician?->user)
            <p class="mt-1 text-xs text-slate-500">برای حفظ رمز فعلی خالی بگذارید.</p>
        @endif
        @error('password')
            <p class="mt-1 text-sm" style="color:#dc2626">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <x-field-label for="password_confirmation" :required="$passwordRequired">تکرار رمز عبور</x-field-label>
        <div style="position: relative;">
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="ui-input"
                style="padding-inline-end: 2.75rem;"
                autocomplete="new-password"
            >
            <button
                type="button"
                data-password-toggle
                aria-label="نمایش تکرار رمز عبور"
                style="position:absolute;inset-inline-end:0;top:0;bottom:0;width:2.75rem;border:0;background:transparent;cursor:pointer;color:#475569;"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            </button>
        </div>
        @error('password_confirmation')
            <p class="mt-1 text-sm" style="color:#dc2626">{{ $message }}</p>
        @enderror
    </div>
</div>

<fieldset class="ui-fieldset">
    <legend class="ui-legend">مهارت‌ها</legend>
    <div class="ui-check-grid">
        @forelse ($skills as $skill)
            <label class="ui-check-pill">
                <input
                    type="checkbox"
                    name="skill_ids[]"
                    value="{{ $skill->id }}"
                    class="ui-checkbox"
                    @checked(in_array($skill->id, old('skill_ids', $technician?->skills?->modelKeys() ?? []), false))
                >
                {{ $skill->name }}
            </label>
        @empty
            <p class="text-sm text-slate-500">مهارتی ثبت نشده است.</p>
        @endforelse
    </div>
</fieldset>

<script>
    (function () {
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            if (button.dataset.bound === '1') {
                return;
            }
            button.dataset.bound = '1';
            button.addEventListener('click', function () {
                var wrap = button.parentElement;
                var input = wrap ? wrap.querySelector('input') : null;
                if (!input) {
                    return;
                }
                var hidden = input.getAttribute('type') === 'password';
                input.setAttribute('type', hidden ? 'text' : 'password');
                button.setAttribute('aria-label', hidden ? 'پنهان کردن رمز عبور' : 'نمایش رمز عبور');
            });
        });

        var mobile = document.getElementById('mobile');
        var mobileError = document.getElementById('mobile-error');
        if (!mobile || !mobileError) {
            return;
        }

        var showMobileError = function () {
            var value = (mobile.value || '').trim();
            if (value === '') {
                mobileError.hidden = true;
                mobile.classList.remove('border-red-500');
                return true;
            }
            if (!value.startsWith('09')) {
                mobileError.textContent = 'شماره همراه باید با ۰۹ شروع شود.';
                mobileError.hidden = false;
                mobile.classList.add('border-red-500');
                return false;
            }
            if (!/^09\d{9}$/.test(value)) {
                mobileError.textContent = 'شماره همراه باید ۱۱ رقم و فقط عدد باشد.';
                mobileError.hidden = false;
                mobile.classList.add('border-red-500');
                return false;
            }
            mobileError.hidden = true;
            mobile.classList.remove('border-red-500');
            return true;
        };

        mobile.addEventListener('input', showMobileError);
        mobile.addEventListener('blur', showMobileError);
        var form = mobile.closest('form');
        if (form) {
            form.addEventListener('submit', function (event) {
                if (!showMobileError()) {
                    event.preventDefault();
                    mobile.focus();
                }
            });
        }
    })();
</script>
