@if (session('status'))
    <x-alert type="success" class="mb-4">{{ session('status') }}</x-alert>
@endif

@if ($errors->any())
    <x-alert type="danger" class="mb-4">
        <ul class="list-disc space-y-1 ps-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif
