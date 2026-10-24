@php
    $u = $unidade ?? null;
    $tipos = ['secretaria' => 'Secretaria', 'gabinete' => 'Gabinete', 'departamento' => 'Departamento', 'divisao' => 'Divisão', 'setor' => 'Setor'];
@endphp
<x-sigem.card title="Identificação" id="t-ident">
    <div class="grid gap-4 md:grid-cols-6">
        <x-sigem.field label="Nome" for="nome" required class="md:col-span-4">
            <input id="nome" name="nome" type="text" required maxlength="200" value="{{ old('nome', $u?->nome) }}" class="sigem-input" placeholder="Ex.: Secretaria Municipal de Obras">
        </x-sigem.field>
        <x-sigem.field label="Sigla" for="sigla" class="md:col-span-2" help="Aparece nas listas e no histórico.">
            <input id="sigla" name="sigla" type="text" maxlength="30" value="{{ old('sigla', $u?->sigla) }}" class="sigem-input uppercase" placeholder="SEMOSP">
        </x-sigem.field>
        <x-sigem.field label="Tipo" for="tipo" required class="md:col-span-2">
            <select id="tipo" name="tipo" required class="sigem-input">
                @foreach($tipos as $v => $r)<option value="{{ $v }}" @selected(old('tipo', $u?->tipo ?? 'secretaria') === $v)>{{ $r }}</option>@endforeach
            </select>
        </x-sigem.field>
        <x-sigem.field label="Subordinada a" for="parent_id" class="md:col-span-4" help="Deixe em branco para unidades de primeiro nível.">
            <select id="parent_id" name="parent_id" class="sigem-input">
                <option value="">Nenhuma</option>
                @foreach($unidadesPai as $p)
                    <option value="{{ $p->id }}" @selected((string) old('parent_id', $u?->parent_id) === (string) $p->id)>{{ $p->sigla ? $p->sigla . ' — ' : '' }}{{ $p->nome }}</option>
                @endforeach
            </select>
        </x-sigem.field>
    </div>
</x-sigem.card>

<x-sigem.card title="Responsável e contato" id="t-contato">
    <div class="grid gap-4 md:grid-cols-2">
        <x-sigem.field label="Responsável (secretário/chefe)" for="responsavel_id" class="md:col-span-2">
            <select id="responsavel_id" name="responsavel_id" class="sigem-input">
                <option value="">Não definido</option>
                @foreach($usuarios as $usr)
                    <option value="{{ $usr->id }}" @selected((string) old('responsavel_id', $u?->responsavel_id) === (string) $usr->id)>{{ $usr->name }}</option>
                @endforeach
            </select>
        </x-sigem.field>
        <x-sigem.field label="E-mail" for="email">
            <input id="email" name="email" type="email" maxlength="150" value="{{ old('email', $u?->email) }}" class="sigem-input">
        </x-sigem.field>
        <x-sigem.field label="Telefone" for="telefone">
            <input id="telefone" name="telefone" type="text" maxlength="50" value="{{ old('telefone', $u?->telefone) }}" class="sigem-input">
        </x-sigem.field>
        <x-sigem.field label="Ordem de exibição" for="ordem" help="Menor aparece primeiro.">
            <input id="ordem" name="ordem" type="number" min="0" value="{{ old('ordem', $u?->ordem ?? 0) }}" class="sigem-input md:max-w-[140px]">
        </x-sigem.field>
        <div class="flex items-end">
            <input type="hidden" name="ativo" value="0">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="ativo" value="1" @checked(old('ativo', $u?->ativo ?? true))> Unidade ativa</label>
        </div>
    </div>
</x-sigem.card>
