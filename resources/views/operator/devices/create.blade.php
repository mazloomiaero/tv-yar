@extends(\App\Support\OpsRoute::layout())

@section('content')
    <x-page-header title="ثبت دستگاه" subtitle="{{ $customer->customer_code }} — {{ $customer->first_name }} {{ $customer->last_name }}" />

    <x-card>
        <form method="POST" action="{{ \App\Support\OpsRoute::to('customers.devices.store', $customer) }}" class="space-y-4">
            @csrf
            @include('operator.devices._form', ['device' => null])
            <div class="flex gap-2">
                <x-button>ثبت دستگاه</x-button>
                <x-button variant="secondary" href="{{ \App\Support\OpsRoute::to('customers.edit', $customer) }}">بازگشت</x-button>
            </div>
        </form>
    </x-card>
@endsection
