@php
    $status = (string) old('status', $branch?->status ?? 'active');
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-field-label for="name" :required="true">نام</x-field-label>
        <input id="name" type="text" name="name" value="{{ old('name', $branch?->name ?? '') }}" maxlength="150" required class="ui-input">
        @error('name')
            <p class="mt-1 text-sm" style="color:#dc2626">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <x-field-label for="phone" :required="true">تلفن</x-field-label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $branch?->phone ?? '') }}" maxlength="30" required class="ui-input">
        @error('phone')
            <p class="mt-1 text-sm" style="color:#dc2626">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-field-label for="province" :required="true">استان</x-field-label>
        <input id="province" type="text" name="province" value="{{ old('province', $branch?->province ?? '') }}" maxlength="100" required class="ui-input">
        @error('province')
            <p class="mt-1 text-sm" style="color:#dc2626">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <x-field-label for="city" :required="true">شهر</x-field-label>
        <input id="city" type="text" name="city" value="{{ old('city', $branch?->city ?? '') }}" maxlength="100" required class="ui-input">
        @error('city')
            <p class="mt-1 text-sm" style="color:#dc2626">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <x-field-label for="address" :required="true">نشانی</x-field-label>
    <textarea id="address" name="address" required class="ui-textarea">{{ old('address', $branch?->address ?? '') }}</textarea>
    @error('address')
        <p class="mt-1 text-sm" style="color:#dc2626">{{ $message }}</p>
    @enderror
</div>

<div>
    <input type="hidden" name="is_default" value="0">
    <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_default" value="1" class="ui-checkbox" @checked((string) old('is_default', $branch?->is_default ? '1' : '0') === '1')>
        شعبه پیش‌فرض
    </label>
    <p class="mt-1 text-xs text-slate-500">اگر چند شعبه دارید، یکی را به‌عنوان شعبه اصلی این نصب علامت بزنید.</p>
</div>

<div>
    <x-field-label for="status" :required="true">وضعیت</x-field-label>
    <select id="status" name="status" required class="ui-select">
        <option value="active" @selected($status === 'active')>فعال</option>
        <option value="inactive" @selected($status === 'inactive')>غیرفعال</option>
    </select>
    <p class="mt-1 text-xs text-slate-500">فعال یعنی شعبه در کار است. غیرفعال یعنی موقتاً استفاده نمی‌شود (با حذف فرق دارد).</p>
    @error('status')
        <p class="mt-1 text-sm" style="color:#dc2626">{{ $message }}</p>
    @enderror
</div>
