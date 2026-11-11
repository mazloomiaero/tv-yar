@extends(\App\Support\OpsRoute::layout())

@section('content')
    <x-page-header title="بازخورد" />

    <x-card>
        <dl class="ui-dl">
            <div>
                <dt>درخواست</dt>
                <dd>{{ $feedback->serviceRequest?->request_number }}</dd>
            </div>
            <div>
                <dt>امتیاز</dt>
                <dd>{{ $feedback->score }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt>نظر</dt>
                <dd>{{ $feedback->comment }}</dd>
            </div>
        </dl>
    </x-card>
@endsection
