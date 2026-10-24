# Revisão técnica nº 2 e diagnóstico de UI/UX — Phoenix SIGEM

**Data:** 24/10/2026 · **Base revisada:** commit `d7e0b6b` (main = origin/main)

**Como foi feito:** li o código e consultei o banco local em modo somente leitura. Também clonei o repositório num ambiente limpo, rodei `migrate --seed`, subi o servidor e naveguei com login de admin, tirando capturas de 16 telas, inclusive em celular. Recompilei o CSS no clone para comparar o antes e o depois. **Não alterei nenhum arquivo do projeto.**

---

## Parte 1 — O que melhorou desde a revisão anterior

**Resolvido:**

| Item da revisão 1 | Situação |
|---|---|
| C1 — XSS no popup do mapa | ✅ Resolvido. Função `esc()` aplicada no Dashboard e na Zeladoria. |
| C3 — Enumeração por `like` no portal | ✅ Resolvido. A busca agora é por número exato, com `throttle:15,1`. |
| C4 — Autenticidade por `id` | ✅ Resolvido. Exige o hash completo e oculta os metadados de processos sigilosos. |
| C5 — Sigilo sem bloqueio (Protocolo) | ✅ Resolvido na listagem, no detalhe, no download e na busca. O acesso negado gera um 403 e fica registrado na auditoria. |
| C6 — gov.br | ✅ Em grande parte resolvido: PKCE S256, vínculo só por CPF, `session()->regenerate()` e rota correta. |
| Ocultação de módulos só no menu | ✅ Resolvido. Middleware `modulo:` nas rotas de vendas, comercial e financeiro. |
| Duas tabelas de Secretarias | ✅ Resolvido. `/secretarias` agora usa `UnidadeAdministrativa`. |
| Máquina de estados | ✅ Resolvido em Protocolo, Requisição e Viagem, com regras como "não aprovar a própria requisição", saldo insuficiente, CNH vencida e hodômetro que não pode regredir. |
| Saída de estoque abaixo de zero | ✅ Resolvido. Agora lança erro. |
| Seeders e Git | ✅ Resolvido. Os seeders municipais estão no `DatabaseSeeder` e os arquivos de debug e cookies estão no `.gitignore`. |

**Ainda falta corrigir.** Em ordem de impacto:

1. **Nenhum servidor consegue tramitar processos.** O campo `users.unidade_administrativa_id` foi criado, mas os 8 usuários estão com ele vazio e **não existe campo de lotação no cadastro de usuários** (`admin/users`). Hoje só o admin tramita, recebe e conclui. Esse é o próximo passo obrigatório.
2. **A busca global dá erro 500.** `pages/busca/index.blade.php` chama `route('secretarias.show')`, que não existe. Reproduzido com `/busca?q=a`.
3. **Atender uma requisição parcial baixa o estoque em dobro.** Em `RequisicaoMaterial::atender()` e no modal de `show.blade.php`, o valor padrão é a quantidade *solicitada*, não o *saldo pendente*, e `quantidade_atendida` é sobrescrita em vez de somada. Exemplo: um item de 10 atendido com 5 e depois atendido de novo baixa mais 10.
4. **Migration editada no lugar de criar uma nova.** As mudanças de `cascade` para `restrict` foram feitas em `2026_05_04_...frota...`, que já tinha rodado. **O banco local continua com CASCADE**: excluir um veículo ainda apaga viagens e abastecimentos. Crie uma migration nova que altere as FKs. `audit_processos_sigilosos` também continua com `onDelete('cascade')`, e as tabelas de protocolo seguem sem FK.
5. **Falta autorização por papel no Almoxarifado, na Zeladoria e na Frota.** Qualquer servidor aprova a requisição de outra secretaria, atua como almoxarife ou conclui uma demanda de zeladoria. Só o Protocolo checa a lotação.
6. **Zeladoria:** `concluir()` não verifica o status atual, então dá para concluir duas vezes ou concluir uma demanda cancelada. O atesto continua sendo um nome digitado em vez do usuário autenticado. A numeração ZEL ainda usa `max()+1`, sem lock e sem `unique` em `codigo_interno`.
7. **Credenciais:** `admin@admin.com` / `password` continua válido no banco, os usuários de demonstração (`vendedor@`, `atendente@`, `tecnico@erp.test`) seguem ativos e `APP_DEBUG=true` está no `.env` e no `.env.example`.
8. **Cadastro público aberto:** a tela de login mostra "Não tem uma conta? Cadastrar", e a rota `/signup` existe. Num sistema interno de prefeitura, contas devem ser criadas só pelo admin. Remova a rota e o link.
9. **Portal:** a consulta por número exato ainda permite varrer `2026/000001…` (15 por minuto). O ideal é exigir o número **e** uma chave de acesso do comprovante. Os processos públicos mostram o texto integral dos despachos internos.
10. **O repositório GitHub é público.** Tudo bem pela AGPL, mas confira que nunca vai entrar `.env`, `database.sqlite` ou arquivos de `storage/`. Hoje o `.gitignore` cobre isso. Também não há baseline do LivreOS original: o primeiro commit já contém todas as alterações.
11. Ainda não existe pasta `tests/`.

**Inconsistências de dados que aparecem na tela:**

- A coluna "Serviço/Categoria" da Zeladoria mostra `chamado_publico` (a origem) no lugar do nome da categoria.
- A Frota mostra valores crus de enum: `Maquina_pesada • Diesel_s10`, `Utilitario`, `Caminhao`, `PUBLICO`, `Concluido`.
- O veículo MUN4F26 aparece como "Disponível na Garagem" no card e, ao mesmo tempo, "em trânsito" no Diário de Bordo. O seeder gera viagem em andamento sem atualizar o status do veículo.
- **Despacho com data anterior à autuação:** no processo 2026/000001, "Autuado em 17:52" e o despacho de "11:52". O `MunicipalDashboardSeeder` grava `data_envio = now()->subHours(rand(1,24))` com `created_at = now()`. Não é bug de fuso, mas confunde nas demonstrações. Ajuste o seeder.
- O Dashboard conta como zeladoria toda OS que tenha `logradouro` (`orWhereNotNull('logradouro')`), o que mistura OS comuns.

---

## Parte 2 — Diagnóstico de UI/UX

As capturas de antes estão na pasta `docs/ux/capturas-2026-10-24/` (ver o fim do documento).

### 2.1 O problema nº 1 não é de design: o CSS nunca foi recompilado

O Tailwind compila só as classes que encontra nos `.blade.php` **no momento do build**. O CSS em `public_html/build/assets/` é de **09/05/2026**, do LivreOS original. Por isso, toda classe nova usada nos módulos municipais simplesmente não existe:

- O botão **"Receber"** do processo fica invisível (`bg-slate-700` não existe).
- O botão **"Entrar com gov.br"** aparece como uma caixa branca vazia.
- O **hero do Portal do Cidadão** fica com texto branco sobre fundo branco.
- E vários outros detalhes, como cores de status, gradientes e modais.

Recompilei no clone (`npm ci --ignore-scripts && npx vite build`) e essas três telas voltaram ao normal. **Ação:** rodar `npm run build` sempre que mexer em Blade e publicar o `public/build` novo. Melhor ainda: colocar isso no script de deploy.

> Observação: `npm ci` falha no script `vendor-jodit.mjs`. Por isso usei `--ignore-scripts`. Vale corrigir o script.

### 2.2 Problemas de navegação e estrutura

| Onde | Problema | Efeito para o servidor |
|---|---|---|
| Menu lateral | **Todos** os itens são sanfonas, até "Dashboard › Início" e "Ajuda". | Dois cliques para tudo e uma hierarquia que não existe. |
| Menu lateral | Os ícones `home`, `page` e `globe` não existem no conjunto do LivreOS e viram ★. Zeladoria, Frota, Materiais e Serviços usam o mesmo ícone (`task`). | Não dá para reconhecer o módulo pelo ícone. |
| Menu lateral | Os rótulos longos ("Materiais / Almoxarifado / Almoxarifado", "Zeladoria & Serviços Urbanos") quebram em duas linhas e ficam **centralizados**. | A leitura fica truncada e o alinhamento fica quebrado. Há também "Almoxarifado" repetido. |
| Menu lateral | 12 grupos no mesmo nível, sem agrupamento por finalidade. "Portal do Cidadão (Web)" aparece como módulo interno. | O servidor não sabe onde procurar. |
| Cabeçalho | O placeholder da busca e o atalho "Ctrl K" estão sobrepostos ("CtCtrl K…sistema (Processo"). | Parece quebrado já na primeira tela. |
| Cabeçalho | Não mostra a secretaria do usuário logado. | O servidor não sabe "em nome de qual secretaria" está agindo, e agora isso define permissões. |
| Telas de detalhe | O botão principal muda de lugar e de cor em cada módulo: azul no Protocolo, verde em "Concluir", índigo na Frota. | Não forma memória muscular. |

### 2.3 Problemas de linguagem visual

- **Excesso de "decoração":** etiquetas em CAIXA ALTA ("CENTRO DE COMANDO OPERACIONAL — ATIVO", "MÓDULO D — TRANSPORTE & LOGÍSTICA PÚBLICA") e títulos de marketing ("Painel de Governança & Gestão Municipal"). São 150 usos de `uppercase` nos módulos novos. Para quem usa o sistema 8 horas por dia, isso é ruído.
- **Paleta descontrolada:** cada módulo usa de 12 a 20 famílias de cor (blue, indigo, emerald, amber, rose, purple, violet, orange, teal...). Cada card de KPI tem uma cor própria, então a cor deixa de significar algo.
- **Cantos e sombras inconsistentes:** Portal e Frota usam `rounded-3xl` (80 vezes), o Protocolo usa `rounded-lg`, e há sombra em quase todo card.
- **Densidade baixa nas listas:** na Zeladoria cada linha tem cerca de 140 px de altura porque o relato completo vai na coluna "Serviço". A tela mostra 5 demandas.
- **Tabelas que estouram:** no Dashboard, o botão "Despachar" fica cortado. Na Zeladoria, a coluna "Ação" sai da tela a 1440 px.
- **Avatar com sigla errada:** "Em" (de *Em*presa) no interessado.
- **Português sem acento vindo do banco:** "PUBLICO", "Concluido", "Utilitario". Os enums precisam de um método `label()`.
- **Fonte:** o `app.css` importa a fonte *Outfit* do Google Fonts. No servidor on-premise sem internet ela não carrega e o sistema cai para outra fonte, como já ocorreu no projeto anterior (ADR-0006). Use a fonte do sistema ou hospede a fonte localmente.
- **Leaflet e Alpine vêm do unpkg.com.** Sem internet, o mapa e as interações quebram. Use `npm i leaflet alpinejs` e importe pelo Vite.
- **Mapa:** o centro padrão ainda é Brasília. Use Nonoai (−27,36, −52,77, zoom 14).
- **Portal:** a logo está quebrada (`asset('phoenix_logo.png')` aponta para a raiz do public, mas o arquivo está em `logo/`). Os e-mails são genéricos (`prefeitura@municipio.gov.br`) e o nome "Nonoai" não aparece em nenhum lugar.
- **Login:** o placeholder é `info@gmail.com`, o link "Voltar ao painel" aparece quando o usuário ainda não está logado e há o link "Cadastrar".

### 2.4 O que já funciona bem e deve ser mantido

- A estrutura de **cabeçalho → KPIs → filtros → tabela** se repete nos módulos. Isso é ótimo: basta padronizar.
- Breadcrumb nas telas de detalhe, linha do tempo de tramitação e badges de status.
- Modo escuro já previsto e layout responsivo funcional (no celular, os cards empilham corretamente).

---

## Parte 3 — Proposta: padrão de interface "SIGEM Clean"

Princípio: **o sistema é ferramenta de trabalho, não vitrine.** Cada tela deve responder em 3 segundos a duas perguntas: "o que está pendente comigo?" e "qual é a próxima ação?".

### 3.1 Tokens (um só conjunto, definido em `tailwind.config.js`)

| Token | Valor | Uso |
|---|---|---|
| `primary` | `#1351B4` (azul gov.br) | A **única** cor de ação. Botão principal, links e foco. |
| `ink` / `muted` / `line` / `surface` / `canvas` | `#1B1F24` / `#5B6470` / `#E3E6EA` / `#FFFFFF` / `#F5F6F8` | Texto, texto secundário, bordas, cartões e fundo. |
| Status (só em badges) | `info #1351B4`, `ok #168821`, `warn #B35F00`, `danger #C2241B`, `neutral #5B6470` | Cor **só com significado**: em tramitação, concluído, prazo vencendo, urgente/indeferido, arquivado. |
| Raio | `6px` em controles, `8px` em cartões | Acaba com o `rounded-3xl`. |
| Sombra | nenhuma em cartões, só `border` | Usar sombra apenas em modais e dropdowns. |
| Tipografia | pilha do sistema, base 14 px, títulos 20/16 px semibold, **sem caixa alta** (exceto o cabeçalho de tabela, em 12 px) | |
| Densidade | linha de tabela de 44 px e controles de 36 px | |

### 3.2 Componentes Blade (`resources/views/components/sigem/`)

Hoje cada tela repete de 30 a 60 classes por elemento. Criar 10 componentes elimina a inconsistência na origem:

`<x-sigem.page-header>` (título + subtítulo + 1 ação primária + ações secundárias) · `<x-sigem.kpi>` (número, rótulo e link de filtro, sem ícone colorido) · `<x-sigem.filters>` · `<x-sigem.table>` · `<x-sigem.status :valor>` (lê o enum e já sai com cor e rótulo acentuado) · `<x-sigem.button variant=primary|secondary|danger|ghost>` · `<x-sigem.card>` · `<x-sigem.field>` (label + input + erro + ajuda) · `<x-sigem.timeline>` · `<x-sigem.empty>`.

### 3.3 Menu lateral reorganizado

```
Início                       (link direto)
Minha caixa  [3]             (novo: tudo pendente com a minha secretaria)
─ ATENDIMENTO
  Protocolo
  Zeladoria
─ RECURSOS
  Almoxarifado
  Frota
─ CADASTROS
  Cidadãos
  Secretarias
  Materiais e serviços
─ ADMINISTRAÇÃO
  Usuários e permissões
  Auditoria
  Configurações
─────────
Portal do cidadão ↗          (link externo, no rodapé do menu)
```

- Os itens viram links diretos. A ação "Novo…" vai para o cabeçalho de cada tela, não para o menu.
- Rótulos curtos e alinhados à esquerda, com um ícone distinto por módulo (Heroicons: inbox, document-text, wrench-screwdriver, archive-box, truck, users, building-office, cube, cog).
- O cabeçalho passa a mostrar **"Fulano · SEMOSP"**, e o seletor de lotação fica no menu do usuário.

### 3.4 Mudanças por tela

- **Início (Dashboard):** sai o "Centro de Comando Operacional". Entra **"Bom dia, Fulano — SEMOSP"**, depois a **Minha caixa** (processos a receber, requisições a aprovar e demandas da minha secretaria, cada uma com seu botão de ação) e então 4 KPIs neutros. O mapa fica menor ou vai para a aba Zeladoria.
- **Listas (Protocolo, Zeladoria, Requisições):** uma linha por registro, com o relato truncado em 1 linha e um *tooltip*. A ação vira a linha inteira clicável, sem o botão "Acessar". Os filtros se aplicam ao mudar o valor, sem botão "Filtrar". Filtros rápidos em abas: "Comigo · Minha secretaria · Todos · Atrasados".
- **Detalhe do processo:** uma barra de ações fixa com **uma ação primária contextual**. "Receber" aparece se o processo chegou e não foi recebido, "Tramitar" se já foi recebido, e "Concluir" fica como ação secundária. As outras ações entram num menu "Mais". O prazo aparece com uma cor de status (vence em 2 dias = warn).
- **Formulários:** campos com rótulo em caixa normal e texto de ajuda. A secretaria do usuário vem **pré-selecionada** (requisição, autuação). O botão de enviar fica sempre à direita e diz o que faz ("Enviar para aprovação").
- **Portal:** a identidade de Nonoai entra no cabeçalho (brasão + "Prefeitura de Nonoai"). O hero fica sólido no azul gov.br, sem gradiente. Os "Serviços" viram 3 botões grandes. Os números de transparência ficam abaixo, discretos.
- **Login:** sem o link "Cadastrar", com placeholder `nome@nonoai.rs.gov.br`. O botão gov.br segue o padrão visual oficial.

### 3.5 Ordem de execução sugerida

1. `npm run build` + Leaflet e Alpine via npm + fonte local. **Corrige metade dos defeitos visuais em 1 hora.**
2. Tokens no `tailwind.config.js` e os 10 componentes `x-sigem.*`.
3. Menu e cabeçalho (afeta todas as telas de uma vez).
4. Migrar tela a tela começando por **Protocolo** (a mais usada), depois Requisições, Zeladoria e Frota.
5. Enums com `label()` e `cor()` para eliminar os textos crus e as cores soltas.
6. Revisão de acessibilidade: contraste AA, foco visível, `label for` em todos os campos e navegação por teclado nos modais.

Um protótipo navegável das telas propostas (menu, Início, lista e detalhe do Protocolo) foi publicado junto com esta revisão para servir de referência visual ao implementador.
