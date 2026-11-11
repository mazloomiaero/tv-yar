@extends('layouts.admin')

@section('content')
    <x-page-header title="کارکنان">
        <x-slot:actions>
            <x-button href="{{ route('admin.staff.create') }}">کارمند جدید</x-button>
        </x-slot:actions>
    </x-page-header>

    <p class="mb-4 text-sm text-slate-600">اپراتور، ناظر فنی، حسابدار و مدیر را اینجا تعریف کنید. تکنسین را از منوی «تکنسین‌ها» بسازید تا همزمان حساب ورود داشته باشد.</p>

    @if ($users->isEmpty())
        <x-empty-state>کارمندی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>نام</th>
                <th>نام خانوادگی</th>
                <th>نام کاربری</th>
                <th>نقش</th>
                <th></th>
            </x-slot:head>
            @foreach ($users as $staffUser)
                <tr>
                    <td>{{ $staffUser->givenName() }}</td>
                    <td>{{ $staffUser->familyName() }}</td>
                    <td>{{ $staffUser->username }}</td>
                    <td>
                        @foreach ($staffUser->roles as $role)
                            {{ \App\Support\RoleName::tryFrom($role->name)?->label() ?? $role->name }}
                        @endforeach
                    </td>
                    <td class="text-left ps-8">
                        <div class="flex flex-wrap items-center justify-end gap-4">
                            <a class="text-sm font-semibold hover:underline" style="color:#059669" href="{{ route('admin.staff.edit', $staffUser) }}">ویرایش</a>
                            <form method="POST" action="{{ route('admin.staff.destroy', $staffUser) }}" onsubmit="return confirm('این کارمند حذف شود؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-semibold hover:underline" style="color:#dc2626">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
