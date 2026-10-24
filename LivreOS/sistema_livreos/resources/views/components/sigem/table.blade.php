{{-- Tabela SIGEM com rolagem horizontal própria (a página nunca rola de lado). --}}
@props(['minWidth' => '760px'])
<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'sigem-table']) }} style="min-width: {{ $minWidth }}">
        @isset($head)<thead><tr>{{ $head }}</tr></thead>@endisset
        <tbody>{{ $slot }}</tbody>
    </table>
</div>
