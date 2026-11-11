@extends('layouts.admin')

@section('content')
    <x-page-header title="مهارت‌ها">
        <x-slot:actions>
            <x-button href="{{ route('admin.skills.create') }}">مهارت جدید</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($skills->isEmpty())
        <x-empty-state>مهارتی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>نام</th>
                <th></th>
            </x-slot:head>
            @foreach ($skills as $skill)
                <tr>
                    <td>{{ $skill->name }}</td>
                    <td class="text-left ps-8">
                        <div class="flex flex-wrap items-center justify-end gap-4">
                            <a class="text-sm font-semibold hover:underline" style="color:#059669" href="{{ route('admin.skills.edit', $skill) }}">ویرایش</a>
                            <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" onsubmit="return confirm('این مهارت حذف شود؟')">
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
