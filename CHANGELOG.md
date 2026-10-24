# CHANGELOG

Todas as mudanças notáveis deste projeto serão documentadas neste arquivo.

## [2.1.0-municipal] - 2026-10-25
### Changed
- **Novo padrão de interface "SIGEM Clean"** (`docs/ux/PADRAO-SIGEM-CLEAN.md`): uma cor de ação (azul gov.br), cor de status só com significado, componentes Blade `x-sigem.*`, fonte do sistema (sem Google Fonts), Leaflet/Alpine empacotados (sem CDN).
- Menu lateral agrupado (Atendimento, Recursos, Cadastros, Administração) com links diretos; abas internas por módulo; cabeçalho mostra a lotação do usuário.
- **Início = "Minha caixa"**: processos a receber, processos com o servidor, requisições a aprovar/atender e demandas de zeladoria da secretaria.
- Telas reescritas: Protocolo, Zeladoria, Almoxarifado (requisições e Kardex), Frota, Secretarias, Busca, Portal do Cidadão e login.

### Fixed / Security
- Lotação (secretaria) no cadastro de usuários; chefia (papel *manager*) age só na própria secretaria e não pode alterar lotação nem conceder o papel admin.
- Atendimento parcial de requisição não baixa estoque em dobro (entrega limitada ao pendente, quantidade acumulada).
- Numeração ZEL/REQ/VIA sem corrida (`numeradores` com `SELECT … FOR UPDATE`) e código de OS único.
- Históricos de frota e auditoria deixam de ser apagados em cascata; chaves estrangeiras no Protocolo.
- Portal: consulta de processo exige número + chave de acesso; demandas de zeladoria sem chave mostram só o andamento.
- Usuário desativado perde o acesso imediatamente; cadastro público (`/signup`) desativado; busca global sem erro 500.
- Zeladoria com situações intermediárias (programada, em execução, aguardando material) e atesto pelo usuário autenticado.
- Comando `php artisan sigem:preparar-producao` (desativa usuários de demonstração e troca a senha padrão do admin).
- Testes automatizados (`php vendor/bin/phpunit`): 16 testes de autorização, estoque, numeração e portal.

## [2.0.0-municipal] - 2026-10-24
### Added
- 🏛️ Rebranding institucional completo para **Phoenix - SIGEM** (*Sistema Integrado de Gestão Municipal*).
- **Protocolo Zero Papel**: tramitação digital entre Secretarias, numeração anual, controle estrito de sigilo (Público, Restrito, Confidencial), certidão de autenticidade com hash SHA-256 e QR Code.
- **Portal do Cidadão**: autoatendimento sem necessidade de login, consulta de processos, abertura e acompanhamento de chamados de zeladoria urbana.
- **Autenticação gov.br**: integração via OAuth 2.0 com fluxo seguro PKCE S256, verificação de nonce e vinculação automática de servidor/cidadão por CPF.
- **Zeladoria Urbana Georreferenciada**: mapa interativo com Leaflet/OpenStreetMap, pontos de chamado, sanitização XSS e KPIs em tempo real.
- **Almoxarifado Setorial**: controle de estoque sem saldo negativo, requisição setorial com segregação de funções, triagem e Kardex auditável.
- **Gestão de Frotas & Diário de Bordo**: controle de saída e devolução, validação de CNH do condutor, odômetro progressivo e registro antifraude de abastecimento.
- **Secretarias & Unidades Administrativas**: cadastro unificado de órgãos municipais e vinculação de servidores à lotação funcional.


## [0.1.0-beta] - 2026-04-27
### Added
- 🚀 Lançamento beta do LivreOS (livreos) com módulos ERP principais: Ordem de Serviço, PDV, Financeiro, Estoque, Clientes.
- Instalador para Windows (LivreOS_Beta_1.0_Win_x64.exe) empacotado com ambiente XAMPP portátil.
- Guia de instalação e documentação.
- Licenciado sob AGPL‑3.0.

## [1.0.0] - 2026-05-01
### Added
- Lançamento público inicial do LivreOS.
- Módulos ERP principais: Ordem de Serviço, PDV, Financeiro, Estoque, Clientes.
- Sistema de plugins (hooks similares ao WordPress).
- Interface web responsiva.
- Documentação no README.

### Fixed
- Pequenos bugs relacionados aos scripts de instalação.

### Changed
- Cabeçalho de licença atualizado para AGPL‑3.0.

---

*Mantenha este arquivo atualizado a cada release. Siga o versionamento semântico (MAJOR.MINOR.PATCH).*
