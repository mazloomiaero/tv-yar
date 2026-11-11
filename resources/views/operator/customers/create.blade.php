@extends(\App\Support\OpsRoute::layout())

@section('content')
    <x-page-header title="ثبت مشتری" />

    <x-card>
        <form method="POST" action="{{ \App\Support\OpsRoute::to('customers.store') }}" class="space-y-4">
            @csrf
            @include('operator.customers._form', [
                'customer' => null,
                'mobile' => $mobile,
            ])
            <div class="flex gap-2">
                <x-button>ثبت مشتری</x-button>
                <x-button variant="secondary" href="{{ \App\Support\OpsRoute::to('customers.search') }}">بازگشت</x-button>
            </div>
        </form>
    </x-card>
@endsection
