{{--
 * Componente da aplicação Phoenix - SIGEM
 * Frota — painel "Registrar saída" (diário de bordo). Abre com painel === 'saida'.
 * Parâmetros: $veiculo (fixo, na ficha do veículo) OU $veiculosDisponiveis (escolha no diário);
 *             $motoristas (aptos); $secretarias (opcional).
 *
 * @license AGPL-3.0
 --}}
@php
    $veiculo = $veiculo ?? null;
    $kmVeiculos = $veiculo ? [] : ($veiculosDisponiveis ?? collect())->mapWithKeys(fn ($v) => [$v->id => ['km' => (int) $v->km_atual, 'un' => $v->unidade_medida]]);
    $kmInicial = old('km_saida', $veiculo?->km_atual);
@endphp
<form x-show="painel === 'saida'" x-cloak method="POST" action="{{ route('frota.viagens.store') }}" class="sigem-card mb-5 border-sigem-primary"
    x-data="{ km: @js($veiculo ? null : $kmVeiculos), veiculoId: @js((string) old('veiculo_id', '')), kmSaida: @js($kmInicial !== null ? (string) $kmInicial : '') }">
    @csrf
    <input type="hidden" name="_painel" value="saida">
    <div class="sigem-card-header">
        <div class="min-w-0 flex-1">
            <h2 class="sigem-card-title">Registrar saída</h2>
            <p class="m-0 mt-0.5 text-[13px] text-sigem-muted dark:text-gray-400">Abre a viagem no diário de bordo e marca o veículo como em viagem.</p>
        </div>
    </div>
    <div class="sigem-card-body flex flex-col gap-4">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @if($veiculo)
                <input type="hidden" name="veiculo_id" value="{{ $veiculo->id }}">
            @else
                <x-sigem.field label="Veículo" for="saida_veiculo_id" :error="$errors->first('veiculo_id') ?: null" required class="lg:col-span-2" help="Só aparecem veículos disponíveis.">
                    <select id="saida_veiculo_id" name="veiculo_id" required class="sigem-input" x-model="veiculoId"
                        @change="if (km[veiculoId]) kmSaida = String(km[veiculoId].km)">
                        <option value="">Selecione o veículo</option>
                        @foreach($veiculosDisponiveis as $v)
                            <option value="{{ $v->id }}" @selected((string) old('veiculo_id') === (string) $v->id)>{{ $v->placa }} — {{ $v->marca }} {{ $v->modelo }}</option>
                        @endforeach
                    </select>
                </x-sigem.field>
            @endif
            <x-sigem.field label="Motorista" for="saida_motorista_id" :error="$errors->first('motorista_id') ?: null" required class="lg:col-span-2" help="Motoristas com CNH vencida não podem ser escalados.">
                <select id="saida_motorista_id" name="motorista_id" required class="sigem-input">
                    <option value="">Selecione o motorista</option>
                    @foreach($motoristas as $m)
                        <option value="{{ $m->id }}" @selected((string) old('motorista_id') === (string) $m->id) @disabled($m->isCnhVencida())>
                            {{ $m->nome }} — CNH {{ $m->cnh_categoria }}{{ $m->isCnhVencida() ? ' (vencida em ' . $m->cnh_validade->format('d/m/Y') . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Hodômetro na saída" for="saida_km" :error="$errors->first('km_saida') ?: null" required
                :help="$veiculo ? 'Atual: ' . $veiculo->hodometro_formatado . '.' : 'Preenchido com o hodômetro do veículo escolhido.'">
                <input id="saida_km" name="km_saida" type="number" required step="1" min="{{ $veiculo ? (int) $veiculo->km_atual : 0 }}" x-model="kmSaida"
                    @if(!$veiculo) :min="km[veiculoId] ? km[veiculoId].km : 0" @endif class="sigem-input tabular-nums">
            </x-sigem.field>
            <x-sigem.field label="Data e hora da saída" for="saida_data" help="Deixe como está para registrar agora.">
                <input id="saida_data" name="data_saida" type="datetime-local" value="{{ old('data_saida', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->addHour()->format('Y-m-d\TH:i') }}" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Destino" for="saida_destino" required :class="$veiculo ? 'md:col-span-2 lg:col-span-4' : 'lg:col-span-2'">
                <input id="saida_destino" name="destino" type="text" required maxlength="200" value="{{ old('destino') }}" placeholder="Ex.: Hospital Regional de Frederico Westphalen" class="sigem-input">
            </x-sigem.field>
            @if(!empty($secretarias) && count($secretarias))
                <x-sigem.field label="Secretaria atendida" for="saida_secretaria" class="lg:col-span-2" help="Se não escolher, vale a secretaria do veículo.">
                    <select id="saida_secretaria" name="unidade_administrativa_id" class="sigem-input">
                        <option value="">Secretaria do veículo</option>
                        @foreach($secretarias as $s)
                            <option value="{{ $s->id }}" @selected((string) old('unidade_administrativa_id') === (string) $s->id)>{{ $s->sigla ? $s->sigla . ' — ' : '' }}{{ $s->nome }}</option>
                        @endforeach
                    </select>
                </x-sigem.field>
            @endif
            <x-sigem.field label="Finalidade" for="saida_finalidade" required :class="!empty($secretarias) && count($secretarias) ? 'md:col-span-2' : 'md:col-span-2 lg:col-span-4'">
                <textarea id="saida_finalidade" name="finalidade" rows="2" required class="sigem-input" placeholder="Ex.: transporte de paciente para consulta especializada">{{ old('finalidade') }}</textarea>
            </x-sigem.field>
        </div>
        <fieldset class="m-0 border-0 p-0">
            <legend class="sigem-label">Checklist de saída</legend>
            <div class="flex flex-wrap gap-x-5 gap-y-2">
                @foreach(['check_pneus' => 'Pneus e estepe conferidos', 'check_oleo' => 'Óleo e água conferidos', 'check_docs' => 'Documentos no veículo'] as $campo => $rotulo)
                    <label for="saida_{{ $campo }}" class="flex cursor-pointer items-center gap-2 text-sm">
                        <input type="hidden" name="{{ $campo }}" value="nao_conferido">
                        <input id="saida_{{ $campo }}" type="checkbox" name="{{ $campo }}" value="ok" @checked(old('_painel') !== 'saida' || old($campo) === 'ok')> {{ $rotulo }}
                    </label>
                @endforeach
            </div>
            <p class="sigem-help">Item desmarcado fica registrado como pendência na viagem.</p>
        </fieldset>
        <div class="flex justify-end gap-2">
            <x-sigem.button @click="painel = ''">Cancelar</x-sigem.button>
            <x-sigem.button type="submit" variant="primary">Registrar saída</x-sigem.button>
        </div>
    </div>
</form>
