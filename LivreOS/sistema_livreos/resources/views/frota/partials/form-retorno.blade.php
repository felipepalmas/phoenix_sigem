{{--
 * Componente da aplicação Phoenix - SIGEM
 * Frota — formulário "Registrar retorno" de uma viagem em andamento.
 * Parâmetros: $viagem (com veiculo carregado).
 *
 * @license AGPL-3.0
 --}}
@php
    $chavePainel = 'retorno-' . $viagem->id;
    $deste = old('_painel') === $chavePainel;
    $un = $viagem->veiculo?->unidade_medida ?? 'km';
    $kmSaidaFmt = number_format((int) $viagem->km_saida, 0, ',', '.') . ' ' . $un;
@endphp
<form method="POST" action="{{ route('frota.viagens.retorno', $viagem) }}" class="flex flex-col gap-4">
    @csrf
    <input type="hidden" name="_painel" value="{{ $chavePainel }}">
    <div class="grid gap-4 md:grid-cols-3">
        <x-sigem.field label="Hodômetro no retorno" for="retorno_km_{{ $viagem->id }}" required
            :error="$deste ? ($errors->first('km_retorno') ?: null) : ''" :help="'Na saída: ' . $kmSaidaFmt . '.'">
            <input id="retorno_km_{{ $viagem->id }}" name="km_retorno" type="number" required step="1" min="{{ (int) $viagem->km_saida }}"
                value="{{ $deste ? old('km_retorno') : '' }}" placeholder="{{ (int) $viagem->km_saida }}" class="sigem-input tabular-nums">
        </x-sigem.field>
        <x-sigem.field label="Ocorrências" for="retorno_ocorrencias_{{ $viagem->id }}" class="md:col-span-2" help="Avarias, multas, pneu furado, ruídos. Deixe em branco se não houve.">
            <textarea id="retorno_ocorrencias_{{ $viagem->id }}" name="ocorrencias" rows="2" class="sigem-input">{{ $deste ? old('ocorrencias') : '' }}</textarea>
        </x-sigem.field>
    </div>
    <fieldset class="m-0 border-0 p-0">
        <legend class="sigem-label">Checklist de retorno</legend>
        <div class="flex flex-wrap gap-x-5 gap-y-2">
            @foreach(['check_retorno_pneus' => 'Pneus em ordem', 'check_limpo' => 'Veículo entregue limpo'] as $campo => $rotulo)
                <label for="{{ $campo }}_{{ $viagem->id }}" class="flex cursor-pointer items-center gap-2 text-sm">
                    <input type="hidden" name="{{ $campo }}" value="nao_conferido">
                    <input id="{{ $campo }}_{{ $viagem->id }}" type="checkbox" name="{{ $campo }}" value="ok" @checked(!$deste || old($campo) === 'ok')> {{ $rotulo }}
                </label>
            @endforeach
        </div>
    </fieldset>
    <div class="flex justify-end gap-2">
        <x-sigem.button @click="painel = ''">Cancelar</x-sigem.button>
        <x-sigem.button type="submit" variant="primary">Registrar retorno</x-sigem.button>
    </div>
</form>
