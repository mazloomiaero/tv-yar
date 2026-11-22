<x-layouts.auth title="ورود کارکنان">
    <div class="auth-form-header">
        <span class="auth-form-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <rect x="5" y="11" width="14" height="10" rx="2" />
                <path d="M8 11V8a4 4 0 0 1 8 0v3" />
            </svg>
            پنل کارکنان
        </span>
        <h2 class="auth-form-title">ورود کارکنان</h2>
        <p class="auth-form-subtitle">با نام کاربری و رمز عبور وارد شوید.</p>
    </div>

    <x-auth-method-tabs panel="staff" active="password" />

    <x-flash />

    <form method="POST" action="{{ route('login.store') }}" class="auth-form" novalidate>
        @csrf

        <div>
            <label for="username" class="ui-label">نام کاربری</label>
            <input
                id="username"
                type="text"
                name="username"
                value="{{ old('username') }}"
                required
                autofocus
                class="ui-input @error('username') border-red-500 @enderror"
                autocomplete="username"
                aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}"
                @error('username') aria-describedby="username-error" @enderror
            >
            @error('username')
                <p id="username-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="ui-label">رمز عبور</label>
            <div class="ui-password">
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    class="ui-input @error('password') border-red-500 @enderror"
                    autocomplete="current-password"
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                    @error('password') aria-describedby="password-error" @enderror
                >
                <button
                    type="button"
                    class="ui-password-toggle"
                    data-password-toggle
                    aria-label="نمایش رمز عبور"
                >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </button>
            </div>
            @error('password')
                <p id="password-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <x-button class="auth-submit w-full">ورود به سامانه</x-button>
    </form>
</x-layouts.auth>
