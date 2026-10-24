# Phoenix - SIGEM — Sistema Integrado de Gestão Municipal

**Phoenix - SIGEM** é uma plataforma integrada de gestão pública municipal, desenvolvida para modernização administrativa de prefeituras, câmaras e autarquias públicas.  
Reúne em um único ambiente processos digitais **Zero Papel**, zeladoria urbana colaborativa com mapa georreferenciado, controle de almoxarifado setorial, diário de bordo e controle de frota veicular, portal do cidadão com validação de autenticidade documental por QR Code e autenticação única via **gov.br**.

Baseado e derivado da plataforma LivreOS sob licença livre **GNU AGPL v3.0**.

[![GitHub Repo](https://img.shields.io/badge/GitHub-felipepalmas%2Fphoenix__sigem-blue?logo=github)](https://github.com/felipepalmas/phoenix_sigem)
[![License](https://img.shields.io/badge/license-AGPL--3.0-green)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12-red)](https://laravel.com)

---

## 🏛️ Módulos Municipais Integrados

### 1. Protocolo Zero Papel & Tramitação Digital
- Abertura de processos administrativos digitais com numeração sequencial única anual (`ANO.SEQUENCIAL`).
- Tramitação entre Secretarias e Unidades Administrativas com despacho formal, prazos e anexação de pareceres.
- Classificação de sigilo (Público, Restrito, Confidencial) com controle estrito de permissão de acesso.
- Emissão de comprovante e certidão de autenticidade com hash SHA-256 e QR Code para validação pública.

### 2. Portal do Cidadão & Transparência
- Acompanhamento de demandas e processos pelo cidadão sem necessidade de login.
- Abertura de solicitações de zeladoria urbana com foto, descrição e endereço.
- Consulta e validação de autenticidade documental em tempo real.
- Proteção de privacidade aderente à LGPD com anonimização de dados confidenciais.

### 3. Autenticação gov.br
- Integração oficial via protocolo OAuth 2.0 com fluxo seguro **PKCE S256** e proteção contra *replay attacks* por `nonce`.
- Identificação unificada de servidores e munícipes por CPF validado e nível de confiabilidade da conta gov.br.

### 4. Zeladoria Urbana & Mapa Georreferenciado
- Abertura, triagem, despacho e encerramento de demandas de infraestrutura, saneamento, iluminação e vias públicas.
- Mapa dinâmico integrado (Leaflet/OpenStreetMap) com plotagem de chamados, filtros por bairro e status de atendimento.
- Sanitização rigorosa contra XSS em popups e marcadores geográficos.

### 5. Almoxarifado Setorial & Kardex
- Cadastro de materiais de consumo e bens permanentes com controle de lote e validade.
- Fluxo de requisição setorial com segregação de funções (solicitação, aprovação e entrega por servidores distintos).
- Prevenção nativa contra saldo negativo e estorno auditável com movimentação no Kardex.

### 6. Gestão de Frotas & Diário de Bordo
- Controle de veículos próprios e terceirizados com monitoramento de hodômetro e disponibilidade em tempo real.
- Bloqueio de saída com condutores com CNH vencida ou veículos em manutenção.
- Diário de bordo com registro de saída, retorno, rota, finalidade e passageiros.
- Registro e controle antifraude de abastecimentos com validação de capacidade de tanque e cálculo de média de consumo (km/L).

### 7. Secretarias & Organograma Municipal
- Gestão centralizada de Secretarias Municipais e Unidades Administrativas.
- Vinculação de servidores às suas respectivas lotações administrativas para auditoria e controle de tramitação.

---

## 📁 Estrutura do Repositório

```plaintext
phoenix_sigem/
├── LivreOS/
│   ├── public_html/          # Diretório público (assets, index.php, router, logo, favicon)
│   └── sistema_livreos/      # Núcleo da aplicação Laravel (app, config, routes, database)
├── iniciar-phoenix.bat       # Inicializador rápido para ambiente local Windows
├── iniciar-livreos.bat       # Inicializador legado de compatibilidade
├── PLANO-CONVERSAO-PREFEITURA.md  # Arquitetura e especificação dos módulos municipais
├── DECRETO-MUNICIPAL-ZERO-PAPEL.md # Modelo normativo para regulamentação municipal
├── GOVBR-INTEGRACAO.md       # Documentação técnica da integração gov.br
└── LICENSE                   # Licença GNU AGPL-3.0
```

---

## ⚡ Como Iniciar Localmente (Windows)

1. **Via Script Rápido:**
   Dê um duplo clique no arquivo `iniciar-phoenix.bat` (ou `iniciar-livreos.bat`).  
   O servidor PHP interno iniciará apontando para `LivreOS/public_html` na porta 8000.

2. **Acesso no Navegador:**
   Abra: **`http://127.0.0.1:8000`**

3. **Portal do Cidadão:**
   Abra: **`http://127.0.0.1:8000/portal`**

4. **Credenciais Institucionais:**
   Acesse a tela de login utilizando as credenciais administrativas configuradas no seu ambiente.

---

## 🔧 Comandos Úteis do Artisan

No diretório `LivreOS/sistema_livreos`:

```powershell
# Executar migrações do banco de dados
php artisan migrate

# Carregar dados municipais iniciais (Secretarias, Zeladoria, Almoxarifado, Frota)
php artisan db:seed --class=MunicipalSeeder

# Limpar caches de configuração e visualizações
php artisan optimize:clear
```

---

## ⚖️ Licença e Copyright

Este projeto é software livre distribuído sob a licença **GNU Affero General Public License v3.0 (AGPL-3.0)**.  
Veja o arquivo [LICENSE](LICENSE) para o texto integral da licença.

- **Phoenix - SIGEM:** Sistema Integrado de Gestão Municipal ([https://github.com/felipepalmas/phoenix_sigem](https://github.com/felipepalmas/phoenix_sigem)).
- **Base upstream:** Desenvolvido originalmente a partir do projeto **LivreOS** (copyright © 2024–2026 viniciusvams / LivreOS).
