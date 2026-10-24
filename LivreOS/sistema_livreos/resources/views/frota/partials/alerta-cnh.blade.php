{{--
 * Componente da aplicação Phoenix - SIGEM
 * Frota — alerta de CNH vencida ou vencendo em até 30 dias (padrão SIGEM Clean)
 * Recebe $alertasCnh = ['vencidas' => Collection, 'vencendo' => Collection] e $linkMotoristas (bool).
 *
 * @license AGPL-3.0
 --}}
@php
    $listar = fn ($lista) => $lista->take(4)->map(fn ($m) => $m->nome . ' (' . $m->cnh_validade->format('d/m/Y') . ')')->implode(', ')
        . ($lista->count() > 4 ? ' e mais ' . ($lista->count() - 4) : '');
@endphp
@if($alertasCnh['vencidas']->isNotEmpty())
    <div class="sigem-alert sigem-alert-danger mb-4" role="alert">
        <span>
            <strong>{{ $alertasCnh['vencidas']->count() === 1 ? '1 motorista com CNH vencida' : $alertasCnh['vencidas']->count() . ' motoristas com CNH vencida' }}</strong>
            — não podem ser escalados para saídas: {{ $listar($alertasCnh['vencidas']) }}.
            @if(!empty($linkMotoristas))<a href="{{ route('frota.motoristas.index') }}" class="sigem-link">Ver motoristas</a>@endif
        </span>
    </div>
@endif
@if($alertasCnh['vencendo']->isNotEmpty())
    <div class="sigem-alert sigem-alert-warn mb-4" role="status">
        <span>
            <strong>{{ $alertasCnh['vencendo']->count() === 1 ? '1 CNH vence' : $alertasCnh['vencendo']->count() . ' CNHs vencem' }} nos próximos 30 dias:</strong>
            {{ $listar($alertasCnh['vencendo']) }}. Peça a renovação antes do vencimento para não bloquear saídas.
            @if(!empty($linkMotoristas))<a href="{{ route('frota.motoristas.index') }}" class="sigem-link">Ver motoristas</a>@endif
        </span>
    </div>
@endif
