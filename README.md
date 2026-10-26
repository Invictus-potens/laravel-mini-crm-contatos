# Mini CRM de Contatos

API REST para gerenciar contatos e processar, de forma assíncrona, o **score** de cada contato, com atualização em tempo real via WebSocket.

Construída com **Laravel**, aplicando **DDD**, **SOLID** e **TDD**.

---

## Stack

- PHP / Laravel (via **Laravel Sail**)
- **SQLite** como banco de dados
- **Redis** para a fila
- **Laravel Reverb** para o WebSocket
- **PHPUnit** para os testes

---

## Arquitetura

O código é dividido em três camadas. As dependências apontam sempre para dentro: a Infraestrutura conhece a Aplicação e o Domínio, mas o Domínio não conhece ninguém.

```
app/
├── Dominio/Contato/          # Regras de negócio (PHP puro, sem Laravel)
│   ├── Entidades/            # Contact (entidade rica)
│   ├── ObjetosDeValor/       # Email, Phone, Status
│   ├── Score/                # ScoreRule (Strategy), regras e ScoreCalculator
│   ├── Eventos/              # ContactScoreProcessed (evento de domínio)
│   ├── Excecoes/             # ContactNotFoundException
│   └── Repositorios/         # ContactRepository (interface)
├── Aplicacao/Contato/        # Use Cases que orquestram o domínio
└── Infraestrutura/           # Tudo que depende do Laravel
    ├── Persistencia/         # ContactModel (Eloquent) e EloquentContactRepository
    ├── Http/                 # Controller, Form Requests e API Resource
    ├── Filas/                # ProcessContactScoreJob
    ├── Listeners/            # LogContactScore e BroadcastContactScore
    ├── Transmissao/          # ContactScoreBroadcast (canal contacts.{id})
    ├── Observadores/         # ContactObserver (normaliza o telefone no saving)
    └── Provedores/           # ContatoServiceProvider (injeção de dependência)
```

### Principais decisões

- **Entidade rica:** `Contact` não tem setters. As mudanças acontecem por métodos de negócio (`startProcessing`, `completeProcessing`, `fail`), e a entidade impede transições inválidas (ex: finalizar sem estar em `processing`).
- **Value Objects:** `Email` e `Phone` validam a si mesmos na criação. `Status` é um enum com os quatro estados do desafio.
- **Strategy:** cada regra de score é uma classe que implementa `ScoreRule`. O `ScoreCalculator` apenas soma os resultados. Uma regra nova é só uma classe nova, registrada no `ContatoServiceProvider`.
- **Inversão de dependência:** os Use Cases dependem da interface `ContactRepository`. A implementação com Eloquent é ligada no Service Container.
- **Evento de domínio:** o `CalculateScoreUseCase` devolve o evento `ContactScoreProcessed`, e o Job o dispara pelo Laravel. Assim, a camada de Aplicação não depende do framework.
- **Telefone:** o `ContactObserver` normaliza o telefone para apenas dígitos no evento `saving` (ex: `(16) 99999-9999` vira `16999999999`).

### Regras de score

| Regra | Pontos |
|---|---|
| E-mail corporativo (não gmail, hotmail ou yahoo) | +20 |
| E-mail terminado em `.br` | +10 |
| Nome completo (mais de uma palavra) | +10 |
| DDD de São Paulo (11 a 19) | +20 |
| DDD de outro estado | +10 |

O cálculo simula uma carga de 2 segundos (`sleep`). Se alguma regra falhar, o contato termina com status `failed`.

---

## Como rodar

> **Windows:** o Sail precisa do **WSL2** com Ubuntu e do Docker Desktop com a integração WSL ativada. Rode todos os comandos no terminal do Ubuntu.

### 1. Clonar e instalar as dependências

```bash
git clone <url-do-repositorio> mini-crm
cd mini-crm

docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

### 2. Configurar o ambiente

```bash
cp .env.example .env
touch database/database.sqlite
```

### 3. Subir os containers

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

> Dica: crie um alias com `alias sail='./vendor/bin/sail'`.

### 4. Iniciar a fila e o WebSocket

Em dois terminais separados:

```bash
sail artisan queue:work
```

```bash
sail artisan reverb:start
```

A API fica em `http://localhost/api` e o Reverb em `ws://localhost:8080`.

---

## Testes

```bash
sail artisan test
```

- **Unitários** (`tests/Unit`): domínio e aplicação, com o repositório mockado, sem banco e sem Laravel.
- **Integração** (`tests/Feature`): repositório Eloquent, endpoints HTTP, fila, Job, listeners e Service Provider, com SQLite em memória.

O histórico de commits segue o ciclo **red → green → refactor** (`test:` → `feat:` → `refactor:`).

---

## Endpoints

| Método | Rota | Ação |
|---|---|---|
| POST | `/api/contacts` | Cria contato (`pending`, score 0) |
| GET | `/api/contacts` | Lista contatos (paginado, `?page=N`) |
| GET | `/api/contacts/{id}` | Mostra contato |
| PUT | `/api/contacts/{id}` | Atualiza contato |
| DELETE | `/api/contacts/{id}` | Exclui contato (soft delete) |
| POST | `/api/contacts/{id}/process-score` | Enfileira o processamento do score |

### Exemplo de fluxo

```bash
curl -X POST http://localhost/api/contacts \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"name":"Felipe Ortiz","email":"felipe@empresa.com.br","phone":"(16) 99999-9999"}'

curl -X POST http://localhost/api/contacts/1/process-score \
  -H "Accept: application/json"

curl http://localhost/api/contacts/1 -H "Accept: application/json"
```

O processamento passa por `processing` e termina em `active` com score **60**. O resultado é gravado em `storage/logs/contact.log` e transmitido no canal `contacts.1`.

---

## Escutando o WebSocket

Salve como `index.html`, abra no navegador e dispare o `process-score` do contato 1. A chave precisa ser igual à `REVERB_APP_KEY` do `.env`.

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Mini CRM - Score em tempo real</title>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8/dist/web/pusher.min.js"></script>
</head>
<body>
    <h1>Contato #<span id="contato">1</span></h1>
    <p>Status: <strong id="status">aguardando...</strong></p>
    <p>Score: <strong id="score">-</strong></p>

    <script>
        const contactId = 1;

        const pusher = new Pusher('mini-crm-key', {
            wsHost: 'localhost',
            wsPort: 8080,
            forceTLS: false,
            enabledTransports: ['ws'],
            cluster: 'mt1',
        });

        const channel = pusher.subscribe(`contacts.${contactId}`);

        channel.bind('contact.updated', (data) => {
            document.getElementById('status').textContent = data.status;
            document.getElementById('score').textContent = data.score;
            console.log('Contato atualizado:', data);
        });
    </script>
</body>
</html>
```

O Reverb usa o mesmo protocolo do Pusher, por isso o exemplo usa a biblioteca `pusher-js` diretamente.