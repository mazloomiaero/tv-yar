@extends(\App\Support\OpsRoute::layout())

@section('content')
    <x-page-header title="مشتریان" subtitle="با شماره همراه، نام یا نام خانوادگی جستجو کنید. درخواست تعمیر از صفحه همان مشتری ثبت می‌شود.">
        <x-slot:actions>
            @can('create', App\Models\Customer::class)
                <x-button href="{{ \App\Support\OpsRoute::to('customers.create', array_filter(['mobile' => $mobile])) }}">افزودن مشتری</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ \App\Support\OpsRoute::to('customers.search') }}" class="mb-4">
        <x-form-shell kicker="جستجو" title="پیدا کردن مشتری" subtitle="شماره همراه یا نام را وارد کنید.">
            <div>
                <label for="q" class="ui-label">شماره همراه، نام یا نام خانوادگی</label>
                <input id="q" type="text" name="q" value="{{ old('q', $query) }}" maxlength="150" autofocus class="ui-input">
            </div>
            <x-slot:actions>
                <x-button>جستجو</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    @if ($searched)
        @if ($customers->isEmpty())
            <x-empty-state>
                @if ($mobile !== '')
                    <p>مشتری با این شماره یافت نشد.</p>
                    <p class="mt-3">
                        <a class="ui-link" href="{{ \App\Support\OpsRoute::to('customers.create', ['mobile' => $mobile]) }}">ایجاد مشتری با این شماره</a>
                    </p>
                @else
                    <p>مشتری با این نام یافت نشد.</p>
                    <p class="mt-3">
                        <a class="ui-link" href="{{ \App\Support\OpsRoute::to('customers.create') }}">ثبت مشتری</a>
                    </p>
                @endif
            </x-empty-state>
        @else
            <x-table>
                <x-slot:head>
                    <th>کد</th>
                    <th>نام</th>
                    <th>همراه</th>
                    <th></th>
                </x-slot:head>
                @foreach ($customers as $customer)
                    <tr>
                        <td>{{ $customer->customer_code }}</td>
                        <td>{{ $customer->first_name }} {{ $customer->last_name }}</td>
                        <td>{{ $customer->mobile }}</td>
                        <td class="text-left">
                            <a class="ui-link" href="{{ \App\Support\OpsRoute::to('customers.edit', $customer) }}">ویرایش / درخواست</a>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    @elseif ($customers->isNotEmpty())
        <h2 class="mb-3 text-sm font-semibold text-slate-700">آخرین مشتریان</h2>
        <x-table>
            <x-slot:head>
                <th>کد</th>
                <th>نام</th>
                <th>همراه</th>
                <th></th>
            </x-slot:head>
            @foreach ($customers as $customer)
                <tr>
                    <td>{{ $customer->customer_code }}</td>
                    <td>{{ $customer->first_name }} {{ $customer->last_name }}</td>
                    <td>{{ $customer->mobile }}</td>
                    <td class="text-left">
                        <a class="ui-link" href="{{ \App\Support\OpsRoute::to('customers.edit', $customer) }}">ویرایش / درخواست</a>
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
