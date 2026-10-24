# Revisão técnica — Phoenix SIGEM sobre base LivreOS

**Data:** 24/10/2026 · **Escopo:** os 176 arquivos alterados ou criados após o fork do LivreOS (tudo o que tem data posterior a 09/05). A revisão leu o código e consultou o `database.sqlite` em modo somente leitura. Nada foi alterado.

## Resumo

A adaptação avançou bastante em pouco tempo: são 6 módulos novos com telas, migrations e seeders. Mas ela está no estágio de **protótipo navegável**. Não é "100% operacional e homologado", como diz o `PLANO-CONVERSAO-PREFEITURA.md`.

Há três problemas estruturais que se repetem em todos os módulos:

1. **Não existe autorização por unidade.** Qualquer usuário com acesso operacional faz tudo: tramita e conclui qualquer processo, aprova a própria requisição, vê processo sigiloso. A tabela `users` nem tem vínculo com `unidades_administrativas`.
2. **Não existe máquina de estados.** Nenhuma ação confere o status atual antes de agir. É possível atender a mesma requisição duas vezes (baixa em dobro), tramitar um processo já deferido (ele volta a "tramitando") ou registrar o retorno de uma viagem duas vezes.
3. **O vocabulário de status muda de uma tela para outra.** A Zeladoria grava `"Registrada / Triagem"`, o Portal grava `"Aberta"`, o Dashboard conta `"Registrada"` e o Portal conta `"concluida"`. O resultado são indicadores zerados ou errados.

O documento de plano também diverge da realidade: a pasta **não é um repositório Git** (o item 0.1 está marcado como feito), e os "testes homologados" são scripts que conferem se um `.md` contém certas palavras e que **alteram dados reais**. O `test_fase7_security.php`, por exemplo, marca o primeiro processo do banco como sigiloso.

---

## 1. Críticos: corrigir antes de qualquer uso com dado real

| # | Onde | Problema | Consequência |
|---|---|---|---|
| C1 | `pages/dashboard/index.blade.php:488`, `zeladoria/index.blade.php:242` | O popup do mapa monta HTML concatenando `relato`, `logradouro` e `bairro` sem escapar. | **XSS armazenado sem login.** Um cidadão abre um chamado no portal público com `<img src=x onerror=...>` no relato, e o script roda na sessão do administrador quando ele abre o Dashboard. |
| C2 | `BuscaGlobalController` (linhas 102, 148-149, 158-160, 170 e 127) | A busca consulta colunas que não existem: `clientes.cpf_cnpj`, `clientes.email`, `clientes.telefone`, `produtos.codigo`, `produtos.referencia`, `unidades_administrativas.responsavel_nome` e `ordem_servicos.solicitante_id`. | **A busca global (Ctrl+K) dá erro 500** para administradores e gerentes. O próprio `busca_debug.txt` é uma resposta 500. A busca da listagem do Protocolo (`cpf_cnpj`) também quebra. |
| C3 | `PortalCidadaoController::consultarProcesso` | A busca pública usa `like "%{$numero}%"` e devolve o primeiro resultado. | **Enumeração sem login.** Digitar "1", "2" ou "2026" abre processos públicos com descrição completa, todos os despachos internos e os títulos dos documentos. |
| C4 | `PortalCidadaoController::verificarAutenticidade` | Aceita `orWhere('id', $termo)`. | Testar 1, 2, 3... revela o título do documento e o **assunto do processo, inclusive de processos sigilosos**. |
| C5 | `ProtocoloController::index/show` e `BuscaGlobalController` | O sigilo só gera um registro de auditoria. Não bloqueia nada. | Processos sigilosos aparecem na listagem, na busca e no detalhe para qualquer servidor. A "trilha LAI/LGPD" só registra o vazamento. |
| C6 | `GovBrAuthController::callback` | Identifica o servidor por **CPF ou e-mail**. Não usa PKCE e não valida `nonce` nem o `id_token`. Não chama `session()->regenerate()` depois do login. Redireciona para `route('dashboard.index')`, que não existe. | Um e-mail igual ao de um servidor dá login como esse servidor. Há risco de *session fixation*. O login de servidor via gov.br sempre falha (RouteNotFound). O gov.br exige PKCE, então a integração real não funciona. A sessão `cidadao_*` gravada no callback não é usada em lugar nenhum. |
| C7 | Raiz e `sistema_livreos/` | `APP_DEBUG=true`. `iniciar-livreos.bat` mostra `admin@admin.com / password`. Usuários de demonstração (`vendedor@erp.test` etc.) continuam ativos. `cookie_test.txt`, `cookies_search_test.txt` e `scratch/cookie_*.txt` guardam cookies de sessão do admin. | Exposição de credenciais e de *stack traces* em qualquer rede que alcance o servidor. |

## 2. Altos: corrompem dados ou indicadores

**Almoxarifado**

- **O saldo é global por produto, não por almoxarifado.** `MovimentacaoEstoque::registrar` altera `produtos.estoque_quantidade` e **sobrescreve `estoque_deposito_id`** com o último depósito usado. A expressão "almoxarifados setoriais" no plano não corresponde ao modelo de dados: uma saída da SEMS reduz o saldo da SEMOSP.
- **A saída usa `max(0, saldo - qtd)`.** Uma baixa de 10 com saldo 3 grava "saída 10" no Kardex e zera o saldo, sem erro. Assim o Kardex deixa de bater com o saldo.
- O sistema não confere se a quantidade atendida é ≤ à solicitada nem se é ≤ ao saldo.
- `aprovar`, `recusar`, `atender` e `confirmarRecebimento` não checam a situação atual. Por isso dá para atender uma requisição pendente sem aprovação, atender duas vezes, recusar depois de atendida ou aprovar o próprio pedido (não há segregação de funções).
- O Kardex não registra valor nem custo médio. O balancete do almoxarifado (MCASP/TCE-RS) precisa de valor.
- A numeração `REQ-AAAA/NNNNN` usa `max('id')` do ano em vez de um sequencial. Ela pula números e não reinicia corretamente na virada do ano.

**Protocolo**

- `gerarProximoNumero()` trava dentro de uma transação que termina **antes** do `create()`. Duas autuações simultâneas pegam o mesmo número, e a segunda falha no `unique`. No SQLite o `lockForUpdate` nem tem efeito.
- O `store` não está numa transação: processo, tramitação inaugural e documento são gravados separadamente.
- `receber` aceita qualquer usuário, mesmo de outra unidade. `concluir` pode ser executado várias vezes.
- Arquivar gera um parecer "desfavorável". Os contadores ignoram o status `suspenso`, e o Portal conta `status = 'concluido'`, que não existe.
- As tabelas de protocolo não têm FK, e `audit_processos_sigilosos` usa `onDelete('cascade')`. Ou seja, **apagar o processo apaga a trilha de auditoria**.

**Zeladoria**

- Há dois geradores de código diferentes. O portal usa `count()+1` e o interno usa `max(numero_sequencial)+1`, sem `unique` em `codigo_interno` e compartilhando `numero_sequencial` com as OS do LivreOS. O resultado são códigos duplicados, e `acompanharDemanda()->first()` pode mostrar ao cidadão a demanda de outra pessoa.
- Só existem dois estados, "abrir" e "concluir". Os status Programada, Em execução e Aguardando material não têm tela nem ação.
- O "atesto" é um texto livre digitado (`atesto_responsavel`), não o usuário autenticado. Isso não comprova quem atestou.
- O portal associa o chamado a um cidadão existente só pelo CPF informado, sem validar dígitos. Não há *rate limit* nem captcha no POST público.

**Frota**

- A saída não confere se o veículo já está em viagem ou em manutenção, se a CNH do motorista está vencida ou é de categoria incompatível, nem se `km_saida ≥ km_atual`. O odômetro pode "voltar".
- O retorno pode ser registrado duas vezes. Não há verificação de litros abastecidos acima da capacidade do tanque, que é o principal alerta de fraude.
- `cascadeOnDelete` em viagens, abastecimentos e manutenções faz com que **excluir um veículo apague todo o histórico de gastos**.

**Estrutura**

- **Há duas tabelas de "Secretarias".** A tela `/secretarias` edita `centros_custos`, enquanto Protocolo, Almoxarifado, Frota e Zeladoria usam `unidades_administrativas`. Uma secretaria criada pela tela não aparece em nenhum módulo.
- O Dashboard conta **todas** as 108 OS, inclusive as de demonstração comercial do LivreOS ("Aguardando peças", "Aprovado pelo cliente"), como se fossem zeladoria.
- Os seeders municipais (`Municipal`, `Zeladoria`, `Almoxarifado`, `Frota`, `MunicipalDashboard`) não estão no `DatabaseSeeder`. Uma instalação nova sobe sem eles.

## 3. Médios e baixos

- **A ocultação de módulos é só visual.** `config/modulos.php` esconde o menu, mas as rotas `/financeiro`, `/pedidos-venda` e `/propostas-comerciais` continuam respondendo. Crie um middleware `modulo:financeiro`.
- Não há validação de tipo de arquivo nos anexos do processo (só o tamanho). Defina uma lista de extensões permitidas, como PDF, imagens e ODT/DOCX.
- O mapa usa Brasília como centro padrão e carrega tiles e Leaflet de CDN. Use Nonoai (≈ −27,36, −52,77) e considere hospedar o Leaflet localmente.
- `Processo::gerarProximoNumero(int $ano = null)` usa nullable implícito, que gera *deprecation* no PHP 8.4.
- Tramitação, almoxarifado e frota alteram tabelas do core (`depositos`, `ordem_servicos`). Isso vai gerar conflito toda vez que você trouxer atualizações do LivreOS original.
- Ficaram arquivos soltos: `0`, `dash_res.html`, `dashboard_test.html`, `response_*.html`, `busca_debug.txt` (950 KB) e `scratch/`. O `laravel.log` tem 17 erros de desenvolvimento, já corrigidos, mas o arquivo não foi limpo.
- **AGPL-3.0:** como o sistema é servido pela rede ao cidadão (portal), a licença exige oferecer o código-fonte modificado. Coloque no rodapé do portal um link para o repositório.

## 4. Melhorias recomendadas, em ordem

**Etapa 0: higiene (1 dia)**

1. Rodar `git init`, fazer um commit do LivreOS original numa branch `upstream` e um commit do estado atual na `main`. Sem isso não há como revisar diffs nem desfazer mudanças.
2. Apagar os arquivos de debug e cookies e limpar o `laravel.log`. Adicionar `scratch/`, `*.sqlite` e `storage/logs` ao `.gitignore`.
3. Definir `APP_DEBUG=false` fora do desenvolvimento, trocar a senha do admin, remover os usuários e as OS de demonstração e tirar as credenciais do `.bat`.
4. Mudar os status do plano de "Concluído/Homologado" para "Protótipo".

**Etapa 1: corrigir o que está quebrado (2–3 dias)**

5. Corrigir as colunas da busca global (C2) e a rota do gov.br (C6).
6. Escapar todo conteúdo dos popups do mapa usando `textContent` ou uma função `esc()` (C1).
7. Portal: consulta só por número exato **mais** uma chave de acesso impressa no comprovante (ou CPF do interessado), sem busca por `id` na autenticidade, e com `throttle:10,1` nas rotas `/portal/*` (C3, C4).
8. Juntar Secretarias numa tabela só (`unidades_administrativas`) e apontar a tela `/secretarias` para ela.

**Etapa 2: o alicerce que faltou (1–2 semanas). É aqui que o sistema vira "de prefeitura"**

9. Adicionar `users.unidade_administrativa_id` (lotação) e criar **Policies do Laravel** por módulo:
   - tramitar e receber só se o usuário estiver lotado na unidade atual;
   - ver sigiloso só para a unidade atual, quem criou e quem tem permissão `protocolo.sigiloso`;
   - aprovar requisição só para o chefe da unidade solicitante, e nunca o próprio solicitante;
   - atender só para o almoxarife do depósito.
10. Criar **enums PHP 8.1** de status por módulo (`StatusProcesso`, `SituacaoRequisicao`, `StatusZeladoria`, `SituacaoViagem`) com um método `podeIrPara()`. Toda ação deve verificar a transição, e Dashboard e Portal devem usar os mesmos enums.
11. Criar uma **tabela de numeradores** (`numeradores`: tipo, ano, último) com `SELECT … FOR UPDATE` dentro da mesma transação do `create`, e `unique` em `codigo_interno`. Isso resolve Protocolo, REQ, VIA e ZEL de uma vez.
12. **Saldo por depósito:** criar a tabela `estoque_saldos (produto_id, deposito_id, quantidade, custo_medio)`, bloquear saídas maiores que o saldo, calcular custo médio ponderado na entrada e gravar o valor no Kardex. É a mesma lógica que já existia no projeto Phoenix em Next.js (`lerSaldoTravado` + `aplicarDeltaSaldo` + teste de corrida) e pode ser portada.
13. **Integridade:** criar FKs nas tabelas de protocolo, usar `restrictOnDelete` e *soft delete* no lugar de `cascade` em todo histórico (viagens, abastecimentos, auditoria, Kardex), e criar auditoria geral de alterações (o LivreOS já tem tabelas `audit_*` que podem ser reaproveitadas).

**Etapa 3: produção**

14. **MySQL desde já.** A ADR-0005 do projeto anterior já tinha decidido isso: a equipe domina MySQL, e SQLite não aguenta escrita concorrente nem respeita `lockForUpdate`. Rode todas as migrations contra MySQL 8.
15. Trocar os scripts `scratch/*.ps1` e `*.php` por **testes PHPUnit/Pest** com `RefreshDatabase` (banco de teste isolado). Os testes mínimos são: autorização por unidade, transições inválidas, saldo nunca negativo, numeração concorrente e enumeração no portal.
16. gov.br de verdade: usar PKCE (S256), validar `nonce` e assinatura do `id_token` (JWKS), regenerar a sessão, vincular servidor **só por CPF** e criar uma sessão própria de cidadão que o portal realmente use.
17. Módulos mostrados no menu devem ser ativados por middleware, não só escondidos.

---

### O que está bem feito e vale manter

- A estrutura dos módulos segue o padrão do LivreOS (controller, model e Blade com `erp_view`), o que facilita a manutenção.
- O SHA-256 dos anexos, o mascaramento de nome e CPF no portal e o bloqueio de visualização de sigilosos **no portal**.
- O Kardex já registra o saldo anterior e posterior. Falta só passar a ser por depósito e ter valor.
- A migration de almoxarifado e frota já usa FKs corretamente, e deve servir de modelo para a de protocolo.
- O `config/termos.php` com o helper `termo()` é uma boa forma de adaptar o vocabulário sem mexer no core.
