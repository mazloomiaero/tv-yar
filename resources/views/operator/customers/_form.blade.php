<div class="space-y-4">
    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label for="customer_code" class="ui-label">کد مشتری</label>
            <input id="customer_code" type="text" name="customer_code" value="{{ old('customer_code', $customer?->customer_code ?? '') }}" maxlength="30" required class="ui-input">
        </div>
        <div>
            <label for="first_name" class="ui-label">نام</label>
            <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $customer?->first_name ?? '') }}" maxlength="100" required class="ui-input">
        </div>
        <div>
            <label for="last_name" class="ui-label">نام خانوادگی</label>
            <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $customer?->last_name ?? '') }}" maxlength="100" required class="ui-input">
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label for="mobile" class="ui-label">شماره همراه</label>
            <input id="mobile" type="text" name="mobile" value="{{ old('mobile', $customer?->mobile ?? $mobile) }}" maxlength="30" required class="ui-input">
        </div>
        <div>
            <label for="mobile_secondary" class="ui-label">همراه دوم</label>
            <input id="mobile_secondary" type="text" name="mobile_secondary" value="{{ old('mobile_secondary', $customer?->mobile_secondary ?? '') }}" maxlength="30" class="ui-input">
        </div>
        <div>
            <label for="email" class="ui-label">ایمیل</label>
            <input id="email" type="text" name="email" value="{{ old('email', $customer?->email ?? '') }}" maxlength="150" class="ui-input">
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label for="national_id" class="ui-label">کد ملی</label>
            <input id="national_id" type="text" name="national_id" value="{{ old('national_id', $customer?->national_id ?? '') }}" maxlength="20" class="ui-input">
        </div>
        <div>
            <label for="status" class="ui-label">وضعیت</label>
            <input id="status" type="text" name="status" value="{{ old('status', $customer?->status ?? '') }}" maxlength="20" required class="ui-input">
        </div>
    </div>

    <div>
        <label for="notes" class="ui-label">یادداشت</label>
        <textarea id="notes" name="notes" class="ui-textarea">{{ old('notes', $customer?->notes ?? '') }}</textarea>
    </div>

    <fieldset class="ui-fieldset">
        <legend class="ui-legend">ورود به پنل مشتری</legend>
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="portal_username" class="ui-label">نام کاربری</label>
                <input
                    id="portal_username"
                    type="text"
                    name="portal_username"
                    value="{{ old('portal_username', $customer?->user?->username ?? '') }}"
                    maxlength="255"
                    class="ui-input"
                    autocomplete="username"
                >
            </div>
            <div>
                <label for="portal_password" class="ui-label">رمز عبور</label>
                <input id="portal_password" type="password" name="portal_password" class="ui-input" autocomplete="new-password">
                @if ($customer?->user)
                    <p class="mt-1 text-xs text-slate-500">برای حفظ رمز فعلی خالی بگذارید.</p>
                @endif
            </div>
            <div>
                <label for="portal_password_confirmation" class="ui-label">تکرار رمز عبور</label>
                <input id="portal_password_confirmation" type="password" name="portal_password_confirmation" class="ui-input" autocomplete="new-password">
            </div>
        </div>
    </fieldset>
</div>
