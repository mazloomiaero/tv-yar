@extends('layouts.customer')

@section('content')
    <x-page-header title="بازخورد برای {{ $serviceRequest->request_number }}" />

    @if ($serviceRequest->feedbacks->isNotEmpty())
        <x-card class="mb-6">
            <ul class="space-y-2 text-sm">
                @foreach ($serviceRequest->feedbacks as $feedback)
                    <li>{{ $feedback->score }} — {{ $feedback->comment }}</li>
                @endforeach
            </ul>
        </x-card>
    @endif

    <x-card>
        <form method="POST" action="{{ route('customer.service-requests.feedback.store', $serviceRequest) }}" class="space-y-4">
            @csrf
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
            <div class="flex gap-2">
                <x-button>ارسال بازخورد</x-button>
                <x-button variant="secondary" href="{{ route('customer.dashboard') }}">بازگشت</x-button>
            </div>
        </form>
    </x-card>
@endsection
