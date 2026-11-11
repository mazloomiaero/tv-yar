@extends(\App\Support\OpsRoute::layout())

@section('content')
    <x-page-header title="بازخورد" />

    @if ($feedbacks->isEmpty())
        <x-empty-state>بازخوردی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>درخواست</th>
                <th>امتیاز</th>
            </x-slot:head>
            @foreach ($feedbacks as $feedback)
                <tr>
                    <td>
                        <a class="ui-link" href="{{ \App\Support\OpsRoute::to('feedback.show', $feedback) }}">
                            {{ $feedback->serviceRequest?->request_number }}
                        </a>
                    </td>
                    <td>{{ $feedback->score }}</td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
