@extends('layouts.admin')

@section('content')
    <x-page-header title="انواع دستگاه">
        <x-slot:actions>
            <x-button href="{{ route('admin.device-types.create') }}">نوع دستگاه جدید</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($deviceTypes->isEmpty())
        <x-empty-state>نوع دستگاهی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>نام</th>
                <th></th>
            </x-slot:head>
            @foreach ($deviceTypes as $deviceType)
                <tr>
                    <td>{{ $deviceType->name }}</td>
                    <td class="text-left ps-8">
                        <div class="flex flex-wrap items-center justify-end gap-4">
                            <a class="text-sm font-semibold hover:underline" style="color:#059669" href="{{ route('admin.device-types.edit', $deviceType) }}">ویرایش</a>
                            <form method="POST" action="{{ route('admin.device-types.destroy', $deviceType) }}" onsubmit="return confirm('این نوع دستگاه حذف شود؟')">
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
