@extends(\App\Support\OpsRoute::layout())

@section('content')
    <x-page-header title="ثبت مشتری" subtitle="ایجاد پرونده مشتری جدید برای ادامه گردش خدمت." />

    <form method="POST" action="{{ \App\Support\OpsRoute::to('customers.store') }}">
        @csrf
        <x-form-shell kicker="مشتری" title="ثبت مشتری" subtitle="اطلاعات تماس و مشخصات پایه را وارد کنید.">
            @include('operator.customers._form', [
                'customer' => null,
                'mobile' => $mobile,
            ])

            <x-slot:actions>
                <x-button>ثبت مشتری</x-button>
                <x-button variant="secondary" href="{{ \App\Support\OpsRoute::to('customers.search') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
