@extends(\App\Support\OpsRoute::layout())

@section('content')
    <x-page-header title="ویرایش دستگاه" subtitle="{{ $customer->customer_code }} — {{ $customer->first_name }} {{ $customer->last_name }}" />

    <form method="POST" action="{{ \App\Support\OpsRoute::to('customers.devices.update', [$customer, $device]) }}">
        @csrf
        @method('PUT')
        <x-form-shell kicker="دستگاه" title="ویرایش دستگاه" subtitle="{{ $device->title }}">
            @include('operator.devices._form', ['device' => $device])

            <x-slot:actions>
                <x-button>ذخیره دستگاه</x-button>
                <x-button variant="secondary" href="{{ \App\Support\OpsRoute::to('customers.edit', $customer) }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection