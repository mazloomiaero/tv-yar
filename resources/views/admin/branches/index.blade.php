@extends('layouts.admin')

@section('content')
    <x-page-header title="شعب">
        <x-slot:actions>
            <x-button href="{{ route('admin.branches.create') }}">شعبه جدید</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($branches->isEmpty())
        <x-empty-state>شعبه‌ای ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>نام</th>
                <th>شهر</th>
                <th>تلفن</th>
                <th></th>
            </x-slot:head>
            @foreach ($branches as $branch)
                <tr>
                    <td>{{ $branch->name }}</td>
                    <td>{{ $branch->city }}</td>
                    <td>{{ $branch->phone }}</td>
                    <td class="text-left ps-8">
                        <div class="flex flex-wrap items-center justify-end gap-4">
                            <a class="text-sm font-semibold hover:underline" style="color:#059669" href="{{ route('admin.branches.edit', $branch) }}">ویرایش</a>
                            <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" onsubmit="return confirm('این شعبه حذف شود؟')">
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
