@extends('layouts.admin')

@section('content')
    <x-page-header title="متن صفحه ورود" subtitle="توضیحات سمت راست فرم‌های ورود کارکنان و مشتری را اینجا ویرایش کنید." />

    <form method="POST" action="{{ route('admin.auth-branding.update') }}">
        @csrf
        @method('PUT')
        <x-form-shell kicker="برندینگ" title="متن معرفی سامانه" subtitle="این متن در پنل سمت راست همه فرم‌های ورود نمایش داده می‌شود. نام شرکت و لوگو از جدول شرکت می‌آید.">
            <div>
                <label for="kicker" class="ui-label">عنوان کوتاه (بالای تیتر)</label>
                <input id="kicker" type="text" name="kicker" value="{{ old('kicker', $branding['kicker']) }}" maxlength="100" required class="ui-input">
                @error('kicker')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="title" class="ui-label">تیتر اصلی</label>
                <input id="title" type="text" name="title" value="{{ old('title', $branding['title']) }}" maxlength="150" required class="ui-input">
                @error('title')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="lead" class="ui-label">توضیح کوتاه</label>
                <textarea id="lead" name="lead" rows="3" maxlength="500" required class="ui-input">{{ old('lead', $branding['lead']) }}</textarea>
                @error('lead')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="trust_1" class="ui-label">نکته اعتماد ۱</label>
                <input id="trust_1" type="text" name="trust_1" value="{{ old('trust_1', $branding['trust_1']) }}" maxlength="150" required class="ui-input">
                @error('trust_1')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="trust_2" class="ui-label">نکته اعتماد ۲</label>
                <input id="trust_2" type="text" name="trust_2" value="{{ old('trust_2', $branding['trust_2']) }}" maxlength="150" required class="ui-input">
                @error('trust_2')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="trust_3" class="ui-label">نکته اعتماد ۳</label>
                <input id="trust_3" type="text" name="trust_3" value="{{ old('trust_3', $branding['trust_3']) }}" maxlength="150" required class="ui-input">
                @error('trust_3')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <x-slot:actions>
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('login') }}" target="_blank" rel="noopener">پیش‌نمایش ورود</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
