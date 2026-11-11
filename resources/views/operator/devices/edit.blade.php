@extends(\App\Support\OpsRoute::layout())

@section('content')
    <x-page-header title="ویرایش دستگاه" subtitle="{{ $customer->customer_code }} — {{ $customer->first_name }} {{ $customer->last_name }}" />

    <x-card>
        <form method="POST" action="{{ \App\Support\OpsRoute::to('customers.devices.update', [$customer, $device]) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('operator.devices._form', ['device' => $device])
            <div class="flex gap-2">
                <x-button>ذخیره دستگاه</x-button>
                <x-button variant="secondary" href="{{ \App\Support\OpsRoute::to('customers.edit', $customer) }}">بازگشت</x-button>
            </div>
        </form>
    </x-card>
@endsection
