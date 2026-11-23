# CNPJ Reader API

## Descrição e objetivo

API Laravel para consultar um CNPJ na BrasilAPI, validar o número e persistir os dados da empresa. Permite listar empresas cadastradas com filtros por UF e município e consultar os dados completos de uma empresa.

## Pré-requisitos

- Docker Desktop com Docker Compose.
- PHP 8.3 ou superior e Composer 2 para configurar o `.env` e executar os testes localmente.
- Acesso à internet para consultar a BrasilAPI.

## Configuração e inicialização

Se ainda não existir um `.env`, crie-o a partir do exemplo. No PowerShell:

```powershell
Copy-Item .env.example .env
```

Gere a chave Laravel se `APP_KEY` estiver vazia:

```powershell
php artisan key:generate
```

O Compose lê o `.env` da raiz. A configuração Docker define a conexão MySQL; opcionalmente, ajuste estas variáveis no `.env`:

```dotenv
APP_PORT=8000
DOCKER_DB_DATABASE=cnpj_reader
DOCKER_DB_USERNAME=cnpj
DOCKER_DB_PASSWORD=senha_local
DOCKER_DB_ROOT_PASSWORD=senha_root_local
```

As senhas padrão são apenas para desenvolvimento local. Não compartilhe nem versione o arquivo `.env`.

Suba os containers e execute as migrations:

```bash
docker compose up --build -d
docker compose exec app php artisan migrate --force
```

A API ficará disponível em `http://localhost:8000`. Os dados do MySQL são mantidos no volume `db_data`; `docker compose down` preserva os dados. Evite `docker compose down -v` se quiser mantê-los.

## Swagger

Com a aplicação em execução, acesse `http://localhost:8000/docs`. A especificação OpenAPI está disponível em `http://localhost:8000/openapi.json`. A interface Swagger UI é carregada pela CDN e requer acesso à internet no navegador.

## Endpoints

Os exemplos abaixo usam `curl.exe` no Windows.

### Cadastrar ou atualizar empresa

Consulta a BrasilAPI, valida o CNPJ e salva os dados recebidos:

```powershell
curl.exe -X POST "http://localhost:8000/api/empresas" `
  -H "Content-Type: application/json" `
  -H "Accept: application/json" `
  -d '{"cnpj":"11222333000181"}'
```

### Listar empresas

```powershell
curl.exe "http://localhost:8000/api/empresas"
```

Filtrar por UF e município:

```powershell
curl.exe "http://localhost:8000/api/empresas?uf=SP&municipio=S%C3%A3o%20Paulo"
```

### Consultar empresa pelo CNPJ

Retorna todos os dados persistidos para o CNPJ:

```powershell
curl.exe "http://localhost:8000/api/empresas/11222333000181"
```

## Testes automatizados

Com as dependências instaladas via Composer:

```bash
php artisan test
```
