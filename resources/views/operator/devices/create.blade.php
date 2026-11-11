@extends(\App\Support\OpsRoute::layout())

@section('content')
    <x-page-header title="ثبت دستگاه" subtitle="{{ $customer->customer_code }} — {{ $customer->first_name }} {{ $customer->last_name }}" />

    <form method="POST" action="{{ \App\Support\OpsRoute::to('customers.devices.store', $customer) }}">
        @csrf
        <x-form-shell kicker="دستگاه" title="ثبت دستگاه" subtitle="نوع، برند و مشخصات دستگاه مشتری را وارد کنید.">
            @include('operator.devices._form', ['device' => null])

            <x-slot:actions>
                <x-button>ثبت دستگاه</x-button>
                <x-button variant="secondary" href="{{ \App\Support\OpsRoute::to('customers.edit', $customer) }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
