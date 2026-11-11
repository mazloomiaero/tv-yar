<div>
    <label for="name" class="ui-label">نام</label>
    <input id="name" type="text" name="name" value="{{ old('name', $brand?->name ?? '') }}" maxlength="150" required class="ui-input">
</div>
