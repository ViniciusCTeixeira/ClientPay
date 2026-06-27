# ClientPay

### Clientes organizados, mensalidades sob controle e cobranças mais ágeis.

O **ClientPay** é uma solução de gestão comercial para agências digitais, desenvolvedores freelancers e pequenos negócios que administram sites e serviços recorrentes.

Em um único painel, a equipe acompanha clientes, projetos, valores contratados, reajustes e vencimentos. O sistema também prepara mensagens personalizadas e abre a conversa diretamente no WhatsApp, tornando a rotina de cobrança mais simples e consistente.

> **Posicionamento do produto:** o ClientPay organiza contratos recorrentes e apoia a cobrança. Ele não funciona como banco, carteira digital ou gateway de pagamento.

---

## Por que usar o ClientPay?

Quando clientes, valores e datas ficam espalhados entre planilhas, agendas e conversas, a operação perde tempo e corre o risco de esquecer cobranças ou aplicar valores incorretos.

O ClientPay centraliza essa rotina para ajudar o negócio a:

- visualizar rapidamente o que está pendente, pago, vencido ou cancelado;
- gerar as mensalidades de toda a carteira de uma só vez;
- manter o valor correto de cada contrato após reajustes;
- padronizar lembretes sem perder a personalização;
- acessar o histórico comercial de cada cliente e site;
- reduzir tarefas manuais na gestão da receita recorrente.

---

## Para quem é

- **Agências digitais** que gerenciam hospedagem, manutenção ou suporte mensal de vários sites.
- **Desenvolvedores e designers freelancers** que precisam acompanhar contratos recorrentes sem depender de planilhas.
- **Pequenos negócios de serviços** que cobram mensalidades associadas a projetos ou ativos digitais.
- **Equipes administrativas e comerciais** que precisam compartilhar uma visão organizada da carteira.

---

## Recursos do produto

| Recurso | Valor para a operação |
| --- | --- |
| Gestão de clientes | Reúne nome, e-mail, WhatsApp e os sites vinculados a cada cliente. |
| Gestão de sites e contratos | Registra domínio, custo de criação e mensalidade atual de cada projeto. |
| Histórico de reajustes | Mantém valores e datas de vigência para que novas mensalidades usem o preço correto. |
| Geração de mensalidades em lote | Cria as cobranças de uma competência para toda a carteira e preserva o vencimento individual já utilizado. |
| Controle de situação | Classifica mensalidades como pendentes, pagas, vencidas ou canceladas. |
| Registro de recebimentos | Armazena data, forma e referência do pagamento. |
| Atualização de vencimentos | Identifica mensalidades vencidas com base na data atual. |
| Painel financeiro | Resume valores pendentes, vencidos e recebidos por competência. |
| Busca, filtros e exportação | Localiza mensalidades por cliente, site, status ou competência e exporta CSV. |
| Mensagens personalizadas | Usa modelos diferentes para pré-vencimento, dia do vencimento e atraso. |
| Integração com WhatsApp | Preenche a mensagem com cliente, site, data e valor e abre a conversa pronta para envio. |
| Acesso por usuários | Separa administradores e operadores, protege o login contra tentativas repetidas e encerra sessões inativas. |
| Arquivamento seguro | Retira clientes e sites da operação sem apagar o histórico financeiro. |

---

## Como funciona na prática

1. **Cadastre o cliente** com seus dados de contato.
2. **Vincule um ou mais sites**, informando os valores de criação e mensalidade.
3. **Registre reajustes** com a data em que cada novo valor passa a valer.
4. **Gere as mensalidades do mês** em lote, sem duplicar cobranças já existentes para a mesma data.
5. **Acompanhe a situação** e atualize cada registro conforme o pagamento.
6. **Envie o lembrete pelo WhatsApp** com uma mensagem adequada ao momento da cobrança.

### Exemplo de uso

Uma agência atende 40 clientes com datas e valores diferentes. No início do mês, ela seleciona a competência e gera as mensalidades da carteira. Durante o período, filtra os registros, acompanha os status e abre no WhatsApp os lembretes já preenchidos. Quando um contrato sofre reajuste, registra o novo valor e sua vigência para manter as próximas competências corretas.

---

## Diferenciais

- **Foco em sites e serviços digitais:** a estrutura relaciona diretamente clientes, projetos e cobranças recorrentes.
- **Operação enxuta:** funciona em ambiente PHP, sem exigir serviços externos ou Composer.
- **Dados sob controle da empresa:** o banco SQLite fica na própria instalação e pode ser copiado para backup.
- **Comunicação adaptável:** os textos de cobrança podem ser editados de acordo com o tom da marca.
- **Implantação simples:** o banco e o usuário administrador são preparados no primeiro acesso.

---

## Visão geral técnica

O ClientPay é uma aplicação web construída em **PHP**, com interface responsiva em **Bootstrap** e banco de dados local **SQLite**. Quase todas as páginas exigem autenticação, e as ações de alteração utilizam proteção CSRF.

### Requisitos

- PHP 8.1 ou superior;
- extensões `pdo_sqlite` e `mbstring`;
- Apache 2.4 recomendado, com `mod_rewrite` e `AllowOverride All`;
- permissão de escrita em `app/storage/`.

Não é necessário instalar dependências com Composer.

### Instalação com Apache

1. Clone ou baixe o projeto para o `DocumentRoot` do servidor.
2. Habilite o `mod_rewrite` e permita o uso do arquivo `.htaccess`.
3. Garanta permissão de escrita no diretório `app/storage/`.
4. Reinicie o Apache e acesse a URL configurada para o projeto.

Para uma avaliação rápida em ambiente local:

```bash
php -S localhost:8080 router.php
```

Depois, acesse `http://localhost:8080/index.php`.

### Primeiro acesso

No primeiro acesso, o sistema cria automaticamente o banco `app/storage/database.sqlite` e um usuário administrador.

As credenciais podem ser definidas antes da inicialização por variáveis de ambiente:

```text
CLIENTPAY_ADMIN_NAME
CLIENTPAY_ADMIN_EMAIL
CLIENTPAY_ADMIN_PASSWORD
```

Se `CLIENTPAY_ADMIN_PASSWORD` não estiver definida, uma senha segura será gerada em `initial_admin_credentials.txt`, no mesmo diretório do banco. Na configuração padrão, o arquivo fica em `app/storage/`. Troque a senha após o primeiro login e remova o arquivo depois de guardar a credencial em local seguro.

O caminho do banco também pode ser personalizado com `CLIENTPAY_DB_PATH`.

Outras configurações disponíveis:

```text
CLIENTPAY_TIMEZONE=America/Sao_Paulo
CLIENTPAY_SESSION_TIMEOUT=1800
CLIENTPAY_DEBUG=0
```

> Use sempre o comando com `router.php` no servidor embutido. Ele impede o acesso HTTP aos arquivos internos. Em produção, mantenha `CLIENTPAY_DEBUG=0`, use HTTPS e prefira configurar `CLIENTPAY_DB_PATH` fora do diretório público do servidor.

---

## Estrutura do projeto

```text
app/
|-- lib/          # Autenticação, banco, validação e utilitários
|-- models/       # Acesso aos dados do sistema
|-- pages/        # Telas e fluxos da aplicação
|-- sql/          # Estrutura inicial do banco
`-- storage/      # Banco SQLite e arquivos gerados
config.php        # Configurações da aplicação
index.php         # Ponto de entrada
.htaccess         # Regras do Apache
router.php        # Proteção para o servidor embutido do PHP
tests/            # Testes de regressão sem dependências externas
```

### Dados e manutenção

- O banco principal fica em `app/storage/database.sqlite`.
- As migrações são executadas automaticamente e preservam os dados existentes.
- Para backup, copie o arquivo do banco com a aplicação sem operações de escrita em andamento.
- A remoção do banco reinicia os dados no próximo acesso; faça isso somente quando um reset completo for realmente desejado.

### Testes

Execute a suíte de regressão com:

```bash
php tests/run.php
```

O repositório também inclui um workflow de integração contínua para PHP 8.1 a 8.4.

---

## Resumo comercial

O ClientPay entrega a uma operação de serviços recorrentes um processo único para **organizar a carteira, gerar mensalidades, acompanhar vencimentos e entrar em contato com o cliente**. É uma alternativa direta para equipes que já ultrapassaram o controle por planilhas, mas ainda não precisam da complexidade de uma plataforma financeira de grande porte.
