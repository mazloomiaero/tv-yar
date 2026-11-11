<div class="ui-table-wrap">
    <table {{ $attributes->class('ui-table') }}>
        @isset($head)
            <thead>
                <tr>{{ $head }}</tr>
            </thead>
        @endisset
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
