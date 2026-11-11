@extends('layouts.customer')

@section('content')
    <x-page-header title="بازخورد" subtitle="برای درخواست {{ $serviceRequest->request_number }}" />

    @if ($serviceRequest->feedbacks->isNotEmpty())
        <x-card class="mb-4">
            <ul class="space-y-2 text-sm">
                @foreach ($serviceRequest->feedbacks as $feedback)
                    <li>{{ $feedback->score }} — {{ $feedback->comment }}</li>
                @endforeach
            </ul>
        </x-card>
    @endif

    <form method="POST" action="{{ route('customer.service-requests.feedback.store', $serviceRequest) }}">
        @csrf
        <x-form-shell kicker="پشتیبانی" title="ارسال بازخورد" subtitle="امتیاز و نظر خود را ثبت کنید.">
            <div>
                <label for="score" class="ui-label">امتیاز</label>
                <input id="score" name="score" type="text" value="{{ old('score') }}" required maxlength="50" class="ui-input">
                @error('score')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="comment" class="ui-label">نظر</label>
                <textarea id="comment" name="comment" maxlength="5000" class="ui-textarea">{{ old('comment') }}</textarea>
                @error('comment')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <x-slot:actions>
                <x-button>ارسال بازخورد</x-button>
                <x-button variant="secondary" href="{{ route('customer.dashboard') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
