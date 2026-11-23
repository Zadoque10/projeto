# CNPJ Reader API

## Descrição e objetivo

API Laravel para consultar um CNPJ na BrasilAPI, validar o número e persistir os dados da empresa. Permite listar empresas cadastradas com filtros por UF e município e consultar os dados completos de uma empresa.

## Pré-requisitos

- Docker Desktop com Docker Compose.
- Acesso à internet para consultar a BrasilAPI.
- PHP 8.3 ou superior e Composer 2 apenas para executar os testes localmente.

## Configuração e inicialização

No PowerShell, crie o `.env` a partir do exemplo:

```powershell
Copy-Item .env.example .env
```

Na primeira inicialização, gere uma chave Laravel dentro do container. O arquivo `.env` é montado para que a chave gerada seja persistida na máquina:

```bash
docker compose build app
docker compose run --rm --no-deps app php artisan key:generate
```

Execute `key:generate` somente enquanto `APP_KEY` estiver vazia. A chave é usada para criptografia e não deve ser compartilhada nem versionada.

O Compose lê o `.env` da raiz e usa MySQL no container. Ajuste as variáveis locais se necessário:

```dotenv
APP_PORT=8000
DOCKER_DB_DATABASE=cnpj_reader
DOCKER_DB_USERNAME=cnpj
DOCKER_DB_PASSWORD=cnpj_local_password
DOCKER_DB_ROOT_PASSWORD=local_root_password
```

Se alterar `APP_PORT`, atualize também `APP_URL`. As senhas do exemplo são apenas para desenvolvimento local. Não use esses valores em produção nem versione o arquivo `.env`.

Suba os containers:

```bash
docker compose up --build -d
```

O container da API aguarda o MySQL ficar saudável e aplica as migrations antes de iniciar o Apache. A API ficará disponível em `http://localhost:8000`. Os dados do MySQL são mantidos no volume `db_data`; `docker compose down` preserva os dados. Evite `docker compose down -v` se quiser mantê-los.

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

## Arquitetura

A aplicação é uma API REST em Laravel. As rotas encaminham as requisições para o controlador de empresas, que valida o CNPJ, consulta a BrasilAPI e persiste os dados recebidos no MySQL usando Eloquent. A resposta completa da consulta é armazenada em JSON, junto com campos usados para listar e filtrar empresas.

Docker Compose executa a aplicação PHP/Apache e o MySQL; ao iniciar, a aplicação aguarda o banco e aplica as migrations. A documentação dos endpoints está disponível em `/docs`, com especificação OpenAPI em `/openapi.json`. Os testes de integração verificam os principais fluxos da API.
