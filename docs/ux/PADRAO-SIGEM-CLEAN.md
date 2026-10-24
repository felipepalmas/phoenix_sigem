# Padrão de interface "SIGEM Clean"

O SIGEM é ferramenta de trabalho do servidor, não vitrine. Cada tela responde em poucos
segundos a duas perguntas: **o que está pendente comigo?** e **qual é a próxima ação?**

Protótipo visual de referência: artifact "SIGEM Clean — Protótipo de Interface".
Telas já convertidas e que servem de exemplo de código: `resources/views/protocolo/*`,
`resources/views/zeladoria/*` e `resources/views/pages/dashboard/index.blade.php`.

## Regras

1. **Uma cor de ação**: azul gov.br `#1351B4` (`sigem-primary`, também `brand-500`).
   No máximo **um** botão primário por tela/painel.
2. **Cor de status só com significado**, sempre em badge (`<x-sigem.status tom="...">`):
   `info` em andamento · `ok` concluído/deferido · `warn` atenção/prazo · `danger` urgente,
   vencido, indeferido · `neutral` arquivado/cancelado. Nada de cores decorativas por módulo.
3. **Bordas, não sombras**: cartões `sigem-card` (borda `sigem-line`, raio 8px). Sombra só em
   menus suspensos e modais.
4. **Sem caixa alta decorativa**. Única exceção: cabeçalho de tabela (`sigem-table`).
5. **Texto do banco nunca cru**: use os rótulos dos models (`status_rotulo`, `sigilo_rotulo`,
   `tipo_formatado`, `StatusZeladoria::rotulo()`...). Nada de `maquina_pesada` ou `PUBLICO`.
6. **Fonte do sistema** (Segoe UI/system-ui). Nada de Google Fonts nem CDN: Leaflet e Alpine
   vêm do bundle (`window.L`, `window.sigemMapa()`, `window.sigemEsc()`).
7. **Densidade de sistema de gestão**: texto 14px, controles 36px (`sigem-input`, `sigem-btn`),
   linha de tabela ~44px, relato truncado em 1 linha na lista (detalhe completo na tela de detalhe).
8. **Ação contextual**: a tela de detalhe mostra só as ações possíveis na situação atual e para
   o usuário atual (permissão + lotação). Formulários de ação abrem como painel na própria página
   (Alpine `x-data="{ painel: '' }"`), não em modal.
9. **Acessibilidade**: todo campo com `<label for>`, botões só com ícone têm `aria-label`,
   SVG decorativo com `aria-hidden="true"`, contraste AA, foco visível.
10. **Mapas**: inicialize dentro de `DOMContentLoaded` (o bundle é `type=module`) com
    `window.sigemMapa(id, {centro, zoom})`; todo texto em popup passa por `sigemEsc()`.

## Componentes Blade (`resources/views/components/sigem/`)

| Componente | Uso |
|---|---|
| `<x-sigem.page-header title subtitle :breadcrumbs>` + slots `badges`, `actions` | Topo de toda tela |
| `<x-sigem.button variant="primary\|secondary\|danger\|ghost" size="sm" href>` | Botões e links-botão |
| `<x-sigem.card title subtitle id :padded>` + slot `actions` | Blocos de conteúdo |
| `<x-sigem.kpi label value hint hint-tone href>` | Indicador numérico neutro |
| `<x-sigem.status tom>` | Situação |
| `<x-sigem.field label for required help>` | Rótulo + controle + ajuda + erro |
| `<x-sigem.table min-width>` + slot `head` | Tabela com rolagem própria |
| `<x-sigem.tabs :items>` | Abas/recortes (itens: name, path, active, count) |
| `<x-sigem.empty title description>` | Estado vazio com próxima ação |
| `<x-sigem.flash />` | Mensagens de sessão e erros de validação |
| `<x-sigem.timeline>` / `<x-sigem.timeline-item>` | Histórico |

Classes utilitárias de apoio (em `resources/css/app.css`): `sigem-input`, `sigem-label`,
`sigem-help`, `sigem-link`, `sigem-muted`, `sigem-dl`, `sigem-alert sigem-alert-{info,ok,warn,danger}`,
`sigem-badge-*`.

## Navegação

- Menu lateral em grupos (Atendimento, Recursos, Cadastros, Administração) com **links diretos**
  (`app/Helpers/MenuHelper.php::getMenuGroups`).
- Páginas internas de um módulo aparecem como **abas no topo** automaticamente
  (`MenuHelper::getModuleTabs`), sem mexer em cada tela.
- O cabeçalho mostra o usuário e sua **lotação**; a busca global abre com Ctrl K.

## Build

O CSS só contém as classes que existem nos `.blade.php` no momento do build. **Toda mudança de
tela exige `npm run build`** e publicar a pasta `public/build` (no Windows: `public_html/build`).
