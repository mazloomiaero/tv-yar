@extends('layouts.admin')

@section('content')
    <x-page-header title="تکنسین‌ها">
        <x-slot:actions>
            <x-button href="{{ route('admin.technicians.create') }}">تکنسین جدید</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($technicians->isEmpty())
        <x-empty-state>تکنسینی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>نام</th>
                <th>نام خانوادگی</th>
                <th>شعبه</th>
                <th>شماره همراه</th>
                <th></th>
            </x-slot:head>
            @foreach ($technicians as $technician)
                <tr>
                    <td>{{ $technician->givenName() }}</td>
                    <td>{{ $technician->familyName() }}</td>
                    <td>{{ $technician->branch?->name }}</td>
                    <td>{{ $technician->user?->mobile ?: $technician->user?->username }}</td>
                    <td class="text-left ps-8">
                        <div class="flex flex-wrap items-center justify-end gap-4">
                            <a class="text-sm font-semibold hover:underline" style="color:#059669" href="{{ route('admin.technicians.edit', $technician) }}">ویرایش</a>
                            <form method="POST" action="{{ route('admin.technicians.destroy', $technician) }}" onsubmit="return confirm('این تکنسین حذف شود؟')">
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
