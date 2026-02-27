# ClientPay – Gerenciador de Clientes, Sites e Mensalidades

Aplicação PHP focada em agências e profissionais que precisam controlar clientes, sites, valores recorrentes e comunicações com os clientes. O projeto usa SQLite como banco local, exige autenticação para quase todas as telas e entrega uma interface simples baseada em Bootstrap.

---

## Funcionalidades
- Autenticação com sessão, troca de senha e gerenciamento de usuários do painel.
- CRUD completo de clientes, sites, templates de mensagem e mensalidades.
- Histórico de planos (Plan History) para registrar reajustes de cada site.
- Geração automática de mensalidades por competência mantendo o dia de vencimento individual.
- Atualização em massa do status das mensalidades com base na data atual.
- Pré-visualização de mensagens (ex.: WhatsApp) usando templates com variáveis.

---

## Requisitos
- PHP 8.1+ com as extensões `pdo_sqlite` e `mbstring`.
- Servidor web compatível (Apache 2.4 recomendado) com `mod_rewrite` habilitado e `AllowOverride All`.
- Permissão de escrita no diretório `app/storage/` para o usuário que executa o servidor.
- Composer **não** é obrigatório; todas as dependências estão no repositório.

---

## Instalação (Apache)
1. Clone ou baixe o projeto e mova os arquivos para o `DocumentRoot` do seu VirtualHost.
2. Certifique‑se de que o `mod_rewrite` está habilitado e que o diretório permite `.htaccess`.
3. Ajuste as permissões de `app/storage/` (ex.: `chown www-data:www-data app/storage -R`).
4. Reinicie o Apache e acesse `http://seu-dominio/`.

### Testando com o servidor embutido do PHP
Para um ambiente local rápido:
```bash
php -S localhost:8080 -t .
```
Depois acesse `http://localhost:8080/index.php`.

---

## Primeiro acesso
- Ao entrar pela primeira vez, o arquivo `app/storage/database.sqlite` é criado automaticamente usando `app/sql/schema.sql`.
- Um usuário administrador inicial é criado automaticamente.
- Se `CLIENTPAY_ADMIN_PASSWORD` não for definida, as credenciais iniciais são gravadas em `app/storage/initial_admin_credentials.txt`.
- Opcionalmente, você pode definir variáveis de ambiente antes do primeiro acesso:
  - `CLIENTPAY_ADMIN_EMAIL`
  - `CLIENTPAY_ADMIN_PASSWORD`
  - `CLIENTPAY_ADMIN_NAME`

---

## Estrutura de diretórios
```
app/
 ├─ lib/          # Helpers (Auth, Database, Validation, Flash, etc.)
 ├─ models/       # Camada de acesso a dados (Client, Site, Invoice, Template…)
 ├─ pages/        # Arquivos PHP de cada tela (?p=clients/index, sites/form...)
 │   ├─ auth/     # Login, logout e troca de senha
 │   ├─ invoices/ # Listagem, geração e atualizações
 │   ├─ users/    # CRUD de usuários do painel
 │   └─ partials/ # Header/Nav/Footer compartilhados
 ├─ sql/          # Schema inicial do banco
 └─ storage/      # Banco SQLite e arquivos gerados
config.php        # Caminhos do app e informações básicas
index.php         # Front controller
.htaccess         # Regras para rodar em Apache
```

---

## Fluxo das principais páginas
| Módulo | Rota (`?p=...`) | Destaques |
|--------|-----------------|-----------|
| Login | `auth/login` | Tela pública; todas as outras exigem sessão. |
| Clientes | `clients/index` | Busca por nome, atalhos para editar/excluir e link para sites do cliente. |
| Sites | `sites/index` | Filtro por cliente ou site, exibe valores de criação e mensalidade atual. |
| Mensalidades | `invoices/index` | Geração em lote (`invoices/generate`), atualização de status (`invoices/update`) e preview de mensagens. |
| Templates | `templates/index` | Definição de textos parametrizados para comunicação. |
| Usuários | `users/index` | CRUD dos usuários internos (nome, e-mail e senha). |

---

## Banco de dados & automações
- **Arquivo**: `app/storage/database.sqlite` (pode ser copiado para backup).
- **Schema**: `app/sql/schema.sql`, inclui estrutura principal e templates iniciais.
- **Limpar/Resetar**: basta remover o arquivo `database.sqlite`; ele será recriado no próximo acesso.
- **Caminho customizado do banco**: configure `CLIENTPAY_DB_PATH` para armazenar o SQLite em outro diretório.
- **Migrações automáticas**: ao iniciar, o sistema aplica migrações pendentes sem apagar dados e registra em `schema_migrations`.
- **Novas alterações de banco**: adicione uma nova entrada versionada em `Database::runMigrations()` (`app/lib/Database.php`).
- **Geração de mensalidades** (`?p=invoices/generate`):
  - Informe a competência (AAAA-MM) e um dia padrão.
  - Cada site usa o último dia de vencimento conhecido; sites sem histórico usam o padrão.
  - Se já existir mensalidade para aquele site e data, a geração é ignorada.
- **Atualização de status** (`?p=invoices/update`):
  - Marca como `overdue` as pendentes vencidas e retorna a `pending` quando a data volta ao futuro.

---

## Desenvolvimento
- **Validação rápida**: `php -l caminho/do/arquivo.php`.
- **Servidor local**: `php -S localhost:8080 -t .`.
- **Logs/erros**: configure `display_errors` no `php.ini` ou no Apache para facilitar o debug.
- **Traduções e textos**: todos os labels estão nos arquivos de `app/pages/`; ajuste conforme necessário.
