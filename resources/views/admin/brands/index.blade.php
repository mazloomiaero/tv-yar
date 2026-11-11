@extends('layouts.admin')

@section('content')
    <x-page-header title="برندها">
        <x-slot:actions>
            <x-button href="{{ route('admin.brands.create') }}">برند جدید</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($brands->isEmpty())
        <x-empty-state>برندی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>نام</th>
                <th></th>
            </x-slot:head>
            @foreach ($brands as $brand)
                <tr>
                    <td>{{ $brand->name }}</td>
                    <td class="text-left ps-8">
                        <div class="flex flex-wrap items-center justify-end gap-4">
                            <a class="text-sm font-semibold hover:underline" style="color:#059669" href="{{ route('admin.brands.edit', $brand) }}">ویرایش</a>
                            <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" onsubmit="return confirm('این برند حذف شود؟')">
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
