# CHANGELOG

Todas as mudanças notáveis deste projeto serão documentadas neste arquivo.

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
