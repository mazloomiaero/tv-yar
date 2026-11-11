<div>
    <label for="name" class="ui-label">نام</label>
    <input id="name" type="text" name="name" value="{{ old('name', $part?->name ?? '') }}" maxlength="150" required class="ui-input">
</div>
<div>
    <label for="code" class="ui-label">کد</label>
    <input id="code" type="text" name="code" value="{{ old('code', $part?->code ?? '') }}" maxlength="50" class="ui-input">
</div>
<div>
    <label for="default_unit_price" class="ui-label">قیمت واحد پیش‌فرض</label>
    <input id="default_unit_price" type="text" name="default_unit_price" value="{{ old('default_unit_price', $part?->default_unit_price ?? '') }}" required class="ui-input">
</div>
<div>
    <label for="unit" class="ui-label">واحد</label>
    <input id="unit" type="text" name="unit" value="{{ old('unit', $part?->unit ?? '') }}" maxlength="20" required class="ui-input">
</div>
<div>
    <label for="status" class="ui-label">وضعیت</label>
    <input id="status" type="text" name="status" value="{{ old('status', $part?->status ?? '') }}" maxlength="20" required class="ui-input">
</div>
