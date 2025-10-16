
# API de Loteamentos e Clientes

Esta API foi desenvolvida em **Laravel** e disponibilizada via **Docker**/**Render**, com banco de dados **PostgreSQL**.  
Ela permite o cadastro e a gestão de **Clientes** e **Lotes**, seguindo o padrão **REST**.

---

## URL Base

[https://api-lote.onrender.com](https://api-lote.onrender.com)

---

## Rotas Disponíveis

### Autenticação

| Método | Rota                 | Descrição                    |
| ------ | -------------------- | ---------------------------- |
| POST   | `/api/auth/login`    | Autentica um usuário         |
| POST   | `/api/auth/logout`   | Revoga o token atual         |
| POST   | `/api/auth/refresh`  | Gera um novo access_token    |

#### Exemplo `POST /api/auth/login`

```json
{
  "username": "admin",
  "password": "admin"
}
```

#### Resposta 200

```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "refresh_token": "def50200b31a8c7f4b8...",
  "refresh_expires_in": 43200
}
```

#### Exemplo `POST /api/auth/refresh`

```json
{
  "refresh_token": "def50200b31a8c7f4b8..."
}
```

#### Resposta 200

```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "refresh_token": "newrefresh123...",
  "refresh_expires_in": 43200
}
```

---

### Clientes

| Método | Rota                 | Descrição                    |
| ------ | -------------------- | ---------------------------- |
| GET    | `/api/clientes`      | Lista clientes (com filtros) |
| GET    | `/api/clientes/{id}` | Detalha um cliente           |
| POST   | `/api/clientes`      | Cria um novo cliente         |
| PUT    | `/api/clientes/{id}` | Atualiza **todos os campos** |
| PATCH  | `/api/clientes/{id}` | Atualiza **parcialmente**    |
| DELETE | `/api/clientes/{id}` | Remove um cliente            |

#### Exemplo `POST /api/clientes`

```json
{
  "nome": "João da Silva",
  "endereco": "Rua A, 123",
  "telefone": "85999999999",
  "email": "joao@email.com",
  "tipo_pessoa": "FISICA",
  "cpf": "12345678901"
}
```

#### Resposta 201

```json
{
  "data": {
    "id": 1,
    "nome": "João da Silva",
    "endereco": "Rua A, 123",
    "telefone": "85999999999",
    "email": "joao@email.com",
    "tipo_pessoa": "FISICA",
    "cpf": "12345678901",
    "cnpj": null,
    "responsavel_nome": null,
    "responsavel_cpf": null,
    "created_at": "2025-09-28 20:25:46",
    "updated_at": "2025-09-28 20:25:46",
  }
}
```

---

### Lotes

| Método | Rota              | Descrição                |
| ------ | ----------------- | ------------------------ |
| GET    | `/api/lotes`      | Lista lotes              |
| GET    | `/api/lotes/{id}` | Detalha um lote          |
| POST   | `/api/lotes`      | Cria um novo lote        |
| PUT    | `/api/lotes/{id}` | Atualiza todos os campos |
| PATCH  | `/api/lotes/{id}` | Atualiza parcialmente    |
| DELETE | `/api/lotes/{id}` | Remove um lote           |

#### Exemplo `POST /api/lotes`

```json
{
  "nome": "Lote 01",
  "num_loteamento": 100,
  "num_lote": 5,
  "num_quadra": 2,
  "area_lote": 250.75
}
```

#### Resposta 201

```json
{
  "data": {
    "id": 1,
    "nome": "Lote 01",
    "num_loteamento": 100,
    "num_lote": 5,
    "num_quadra": 2,
    "area_lote": 250.75,
    "created_at": "2025-09-28 20:25:46",
    "updated_at": "2025-09-28 20:25:46",
  }
}
```

---

## Autenticação

* Todas as rotas (exceto `/api/auth/login` e `/api/auth/refresh`) exigem header:

```markefile
Authorization: Bearer <access_token>
```

* Quando o access_token expira, utilize o refresh_token em `/api/auth/refresh`.
  
---

## Filtros e Paginação

* Todas as listagens aceitam parâmetros:

  * `?nome=Joao`
  * `?tipo_pessoa=FISICA`
  * `?sort=nome&dir=asc`
  * `?per_page=10&page=2`

---

## Validações

* `tipo_pessoa`: deve ser **FISICA** ou **JURIDICA**
* Pessoa física → `cpf` obrigatório (11 dígitos)
* Pessoa jurídica → `cnpj` obrigatório (14 dígitos) + responsável (nome e CPF)
* Emails devem ser válidos

Exemplo de resposta de erro:

```json
{
  "message": "Erro de validação",
  "errors": {
    "cpf": ["O CPF é obrigatório para pessoa física."]
  }
}
```

---

## Como rodar localmente

### Pré-requisitos

* Docker e Docker Compose instalados

### Passos

```bash
git clone https://github.com/Sarapessoa/api-lote.git
cd api-lote
cp .env.example .env
docker-compose up -d --build
```

* API disponível em: [http://localhost:8080](http://localhost:8080)
* Rodar migrations:

```bash
docker exec -it laravel-app php artisan key:generate
docker exec -it laravel-app php artisan migrate
```

---

## Licença

Este projeto é de uso livre para fins de estudo e desenvolvimento.
