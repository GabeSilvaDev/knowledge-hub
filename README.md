# Knowledge Hub API

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![MongoDB](https://img.shields.io/badge/MongoDB-6.0-47A248?style=flat-square&logo=mongodb&logoColor=white)](https://mongodb.com)
[![Pest](https://img.shields.io/badge/Pest-4.1-8BC34A?style=flat-square&logo=pest&logoColor=white)](https://pestphp.com)
[![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?style=flat-square&logo=docker&logoColor=white)](https://docker.com)

> API RESTful moderna para gerenciamento de conhecimento com suporte a artigos versionados, autenticação segura e MongoDB.

## 📖 Sobre o Projeto

Knowledge Hub é uma API robusta desenvolvida com Laravel 12 e MongoDB, projetada para gerenciar conteúdo de conhecimento de forma eficiente e escalável. O projeto implementa padrões modernos de arquitetura, incluindo DTOs, Value Objects, Repository Pattern e Service Layer.

### Principais Funcionalidades

- 🔐 **Autenticação JWT** - Sistema completo com Laravel Sanctum
- 📝 **Gerenciamento de Artigos** - CRUD completo com suporte a múltiplos tipos
- 🕐 **Versionamento Automático** - Histórico completo de alterações em artigos
- 🔄 **Restauração de Versões** - Volte para qualquer versão anterior
- 📊 **Comparação de Versões** - Visualize diferenças entre versões
- 🏷️ **Tags e Categorias** - Organização flexível de conteúdo
- 🎯 **SEO Otimizado** - Metadados completos para otimização
- ⚡ **Performance** - Cache, índices e queries otimizadas
- 🧪 **100% Testado** - Cobertura completa com Pest
- 🐳 **Docker Ready** - Ambiente containerizado

## 🚀 Tecnologias

### Backend

- **Laravel 12.0** - Framework PHP moderno
- **PHP 8.4** - Última versão com recursos avançados
- **MongoDB 6.0** - Banco de dados NoSQL flexível
- **Laravel Sanctum 4.2** - Autenticação API

### Desenvolvimento

- **Pest 4.1** - Framework de testes moderno
- **PHPStan** - Análise estática de código
- **Laravel Pint** - Code style automático
- **Docker & Docker Compose** - Containerização

### Arquitetura

- **Repository Pattern** - Abstração de acesso a dados
- **Service Layer** - Lógica de negócio isolada
- **DTOs** - Transferência de dados tipada
- **Value Objects** - Objetos de valor imutáveis
- **Enums** - Tipos enumerados para estados

## 📋 Pré-requisitos

- Docker & Docker Compose
- Git
- Make (opcional)

## ⚡ Início Rápido

### 1. Clone o repositório

```bash
git clone <repository-url>
cd knowledge-hub
```

### 2. Configure o ambiente

```bash
cp .env.example .env
cp .env.testing.example .env.testing
```

### 3. Inicie os containers

```bash
docker-compose up -d
```

### 4. Instale as dependências

```bash
docker exec -it knowledge-hub-knowledge-hub-1 composer install
```

### 5. Gere a chave da aplicação

```bash
docker exec -it knowledge-hub-knowledge-hub-1 php artisan key:generate
```

### 6. Execute as migrations

```bash
docker exec -it knowledge-hub-knowledge-hub-1 php artisan migrate
```

### 7. Acesse a aplicação

```text
http://localhost:8004/api
```

## 🔑 Autenticação

A API utiliza Laravel Sanctum para autenticação via tokens Bearer.

### Endpoints Principais

```bash
# Registro
POST /api/register

# Login
POST /api/login

# Logout
POST /api/logout

# Perfil
GET /api/user
```

**📚 Documentação Completa:** Veja a seção [Autenticação Sanctum - Detalhes](#-autenticação-sanctum---detalhes) para mais detalhes.

## 📝 Gerenciamento de Artigos

O Knowledge Hub oferece um sistema completo de gerenciamento de artigos com suporte a versionamento automático.

### Recursos de Artigos

- **CRUD Completo**: Criar, listar, visualizar, atualizar e excluir artigos
- **Versionamento Automático**: Cada atualização cria automaticamente uma versão histórica
- **Versionamento Manual**: Criar snapshots manualmente com motivos personalizados
- **Restauração de Versões**: Voltar para qualquer versão anterior
- **Comparação de Versões**: Comparar diferenças entre versões
- **Múltiplos Tipos**: Suporte para artigos, tutoriais, guias e documentação
- **Status Flexível**: draft, published, archived
- **SEO Otimizado**: Metadados, slugs e campos de otimização
- **Tempo de Leitura**: Cálculo automático do tempo estimado de leitura

### Endpoints de Artigos

```bash
# Listar artigos
GET /api/articles

# Criar artigo
POST /api/articles

# Visualizar artigo
GET /api/articles/{id}

# Atualizar artigo (cria versão automaticamente)
PUT /api/articles/{id}

# Deletar artigo
DELETE /api/articles/{id}

# Listar versões
GET /api/articles/{id}/versions

# Criar versão manual
POST /api/articles/{id}/versions

# Restaurar versão
POST /api/articles/{id}/versions/{versionId}/restore

# Comparar versões
POST /api/articles/{id}/versions/compare
```

**📚 Documentação Completa:** Veja a seção [Artigos - Endpoints Detalhados](#-artigos---endpoints-detalhados) e [Sistema de Versionamento - Detalhes](#-sistema-de-versionamento---detalhes) para mais detalhes.

## 🏗️ Arquitetura

### Estrutura de Pastas

```text
app/
├── Contracts/           # Interfaces
├── DTOs/               # Data Transfer Objects
├── Enums/              # Enumerações
├── Exceptions/         # Exceções customizadas
├── Helpers/            # Funções auxiliares
├── Http/
│   ├── Controllers/    # Controllers da API
│   └── Requests/       # Form Requests
├── Models/             # Models Eloquent/MongoDB
├── Repositories/       # Camada de dados
├── Services/           # Lógica de negócio
├── Traits/             # Traits reutilizáveis
└── ValueObjects/       # Objetos de valor
```

### Padrões Implementados

- **Repository Pattern** - Abstração do acesso a dados
- **Service Layer** - Lógica de negócio isolada
- **DTO Pattern** - Transferência de dados tipada
- **Value Objects** - Encapsulamento de valores
- **Traits** - Comportamentos reutilizáveis (ex: Versionable)

## 🧪 Testes

### Executar Testes

```bash
# Todos os testes
docker exec -it knowledge-hub-knowledge-hub-1 ./vendor/bin/pest

# Testes específicos
docker exec -it knowledge-hub-knowledge-hub-1 ./vendor/bin/pest tests/Unit/ArticleVersioningTest.php

# Com cobertura
docker exec -it knowledge-hub-knowledge-hub-1 ./vendor/bin/pest --coverage
```

### Estatísticas

- ✅ **735 testes** passando
- ✅ **>98% cobertura** em componentes críticos
- ✅ Testes unitários e de integração

### Scripts de Demonstração

```bash
# Testar versionamento manualmente
docker exec -it knowledge-hub-knowledge-hub-1 php test-versioning.php
```

## 🗄️ Banco de Dados

### Collections MongoDB

| Collection | Descrição |
|-----------|-----------|
| `users` | Usuários do sistema |
| `articles` | Artigos com versionamento |
| `article_versions` | Histórico de versões |
| `personal_access_tokens` | Tokens Sanctum |

### Acessar Dados

```bash
# Via mongosh
docker exec -it knowledge-hub-mongo-1 mongosh
use knowledge_hub
db.articles.find().pretty()

# Via Laravel Tinker
docker exec -it knowledge-hub-knowledge-hub-1 php artisan tinker
Article::all()
ArticleVersion::all()
```

## 🔧 Desenvolvimento

### Comandos Úteis

```bash
# Acessar container
docker exec -it knowledge-hub-knowledge-hub-1 bash

# Logs
docker logs knowledge-hub-knowledge-hub-1 -f

# Rebuild
docker-compose down && docker-compose up -d --build

# Análise estática
docker exec -it knowledge-hub-knowledge-hub-1 ./vendor/bin/phpstan analyse

# Code style
docker exec -it knowledge-hub-knowledge-hub-1 ./vendor/bin/pint
```

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

### Padrões de Código

- Siga as [PSR-12](https://www.php-fig.org/psr/psr-12/)
- Use Pest para testes
- Execute PHPStan antes de commitar
- Mantenha cobertura de testes >90%

## 📝 Changelog

### [2.0.0] - 2025-11-04

#### ✨ Adicionado

- Sistema completo de gerenciamento de artigos
- Versionamento automático de artigos com trait reutilizável
- Criação manual de versões com motivos personalizados
- Restauração para versões anteriores
- Comparação entre versões
- Suporte a múltiplos tipos (article, tutorial, guide, documentation)
- Sistema de status (draft, published, archived)
- Campos SEO completos
- Cálculo automático de tempo de leitura
- Geração automática de slugs
- 21 testes de versionamento
- Documentação completa do sistema

### [1.0.0] - 2025-10-04

#### ✨ Adicionado

- Configuração inicial Laravel 12
- Integração com MongoDB
- Sistema de autenticação com Sanctum
- Endpoints de API RESTful
- Framework de testes Pest
- Ambiente Docker
- Documentação básica

## 📄 Licença

Este projeto está sob a licença MIT. Veja [LICENSE](LICENSE) para mais detalhes.

## 🆘 Suporte

- 📖 [Documentação de Versionamento](docs/ARTICLE_VERSIONING.md)
- 🧪 [Guia de Testes](docs/TESTING_VERSIONING.md)
- 🐛 [Reportar Bug](../../issues)
- 💡 [Solicitar Feature](../../issues)

## 🙏 Créditos

- [Laravel](https://laravel.com) - Framework PHP
- [MongoDB](https://mongodb.com) - Banco de dados NoSQL
- [Pest](https://pestphp.com) - Framework de testes
- [Docker](https://docker.com) - Containerização

---

## 📚 Documentação Detalhada

<details>
<summary><strong>🔐 Autenticação Sanctum - Detalhes</strong></summary>
<br>

### Configuração

O Laravel Sanctum fornece autenticação simples para SPAs e aplicações móveis.

```php
// config/sanctum.php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1'
    )),
    'expiration' => null, // Tokens não expiram
];
```

### Endpoints Detalhados

#### Registro de Usuário

```bash
POST /api/register
Content-Type: application/json

{
  "name": "João Silva",
  "email": "joao@example.com",
  "username": "joaosilva",
  "password": "senha123",
  "password_confirmation": "senha123"
}
```

**Resposta:**

```json
{
  "access_token": "1|abc123...",
  "token_type": "Bearer"
}
```

#### Login

```bash
POST /api/login
Content-Type: application/json

{
  "email": "joao@example.com",
  "password": "senha123"
}
```

**Resposta:**

```json
{
  "access_token": "1|abc123...",
  "token_type": "Bearer"
}
```

#### Logout

```bash
POST /api/logout
Authorization: Bearer {token}
```

**Resposta:**

```json
{
  "message": "Logged out successfully"
}
```

#### Perfil do Usuário

```bash
GET /api/user
Authorization: Bearer {token}
```

**Resposta:**

```json
{
  "data": {
    "_id": "507f191e810c19729de860ea",
    "name": "João Silva",
    "email": "joao@example.com",
    "username": "joaosilva",
    "created_at": "2025-11-04T10:00:00Z"
  }
}
```

### Segurança

- ✅ Senhas hasheadas com bcrypt
- ✅ Tokens gerados de forma segura
- ✅ Email e username únicos
- ✅ Validação de senha confirmada

</details>

<details>
<summary><strong>📝 Artigos - Endpoints Detalhados</strong></summary>
<br>

#### 📄 Listar Artigos

```bash
GET /api/articles
```

**Resposta:**

```json
{
  "data": [
    {
      "_id": "507f1f77bcf86cd799439011",
      "title": "Introdução ao Laravel",
      "slug": "introducao-ao-laravel",
      "type": "article",
      "status": "published",
      "author_id": "507f191e810c19729de860ea",
      "excerpt": "Aprenda os fundamentos do Laravel...",
      "reading_time": 5,
      "created_at": "2025-01-04T10:00:00Z",
      "updated_at": "2025-01-04T10:00:00Z"
    }
  ]
}
```

#### ➕ Criar Artigo

```bash
POST /api/articles
Authorization: Bearer {seu-token}
Content-Type: application/json
```

**Body:**

```json
{
  "title": "Introdução ao Laravel",
  "content": "Laravel é um framework PHP moderno...",
  "excerpt": "Aprenda os fundamentos do Laravel",
  "type": "article",
  "status": "draft",
  "tags": ["laravel", "php", "framework"],
  "categories": ["backend", "web"],
  "seo_title": "Laravel - Guia Completo para Iniciantes",
  "seo_description": "Tutorial completo sobre Laravel"
}
```

**Resposta:**

```json
{
  "data": {
    "_id": "507f1f77bcf86cd799439011",
    "title": "Introdução ao Laravel",
    "slug": "introducao-ao-laravel",
    "content": "Laravel é um framework PHP moderno...",
    "type": "article",
    "status": "draft",
    "author_id": "507f191e810c19729de860ea",
    "reading_time": 5,
    "created_at": "2025-01-04T10:00:00Z",
    "updated_at": "2025-01-04T10:00:00Z"
  }
}
```

#### 🔍 Visualizar Artigo

```bash
GET /api/articles/{id}
```

**Resposta:**

```json
{
  "data": {
    "_id": "507f1f77bcf86cd799439011",
    "title": "Introdução ao Laravel",
    "slug": "introducao-ao-laravel",
    "content": "Laravel é um framework PHP moderno...",
    "excerpt": "Aprenda os fundamentos do Laravel",
    "type": "article",
    "status": "published",
    "author_id": "507f191e810c19729de860ea",
    "tags": ["laravel", "php"],
    "categories": ["backend"],
    "reading_time": 5,
    "views_count": 150,
    "created_at": "2025-01-04T10:00:00Z",
    "updated_at": "2025-01-04T12:30:00Z"
  }
}
```

#### ✏️ Atualizar Artigo (com versionamento automático)

```bash
PUT /api/articles/{id}
Authorization: Bearer {seu-token}
Content-Type: application/json
```

**Body:**

```json
{
  "title": "Introdução ao Laravel 12",
  "content": "Laravel 12 traz novidades incríveis...",
  "status": "published"
}
```

**Nota:** Uma versão do artigo é criada automaticamente antes da atualização.

#### 🗑️ Deletar Artigo

```bash
DELETE /api/articles/{id}
Authorization: Bearer {seu-token}
```

**Resposta:**

```json
{
  "message": "Article deleted successfully"
}
```

</details>

<details>
<summary><strong>🕐 Sistema de Versionamento - Detalhes</strong></summary>
<br>

### Como Funciona

O sistema de versionamento é implementado através do trait `Versionable` que:

- ✅ Cria automaticamente uma versão antes de cada `update()`
- ✅ Registra o autor da versão
- ✅ Incrementa o número da versão
- ✅ Armazena snapshot completo dos dados

#### 🕐 Listar Versões de um Artigo

```bash
GET /api/articles/{id}/versions
Authorization: Bearer {seu-token}
```

**Resposta:**

```json
{
  "data": [
    {
      "_id": "507f1f77bcf86cd799439012",
      "article_id": "507f1f77bcf86cd799439011",
      "version_number": 2,
      "title": "Introdução ao Laravel 12",
      "content": "Laravel 12 traz novidades...",
      "reason": "Atualização automática",
      "created_at": "2025-01-04T12:30:00Z"
    },
    {
      "_id": "507f1f77bcf86cd799439013",
      "article_id": "507f1f77bcf86cd799439011",
      "version_number": 1,
      "title": "Introdução ao Laravel",
      "content": "Laravel é um framework...",
      "reason": "Versão inicial",
      "created_at": "2025-01-04T10:00:00Z"
    }
  ]
}
```

#### 💾 Criar Versão Manual

```bash
POST /api/articles/{id}/versions
Authorization: Bearer {seu-token}
Content-Type: application/json
```

**Body:**

```json
{
  "reason": "Backup antes de grande refatoração"
}
```

**Resposta:**

```json
{
  "data": {
    "_id": "507f1f77bcf86cd799439014",
    "article_id": "507f1f77bcf86cd799439011",
    "version_number": 3,
    "reason": "Backup antes de grande refatoração",
    "created_at": "2025-01-04T14:00:00Z"
  }
}
```

#### ↩️ Restaurar Versão

```bash
POST /api/articles/{id}/versions/{versionId}/restore
Authorization: Bearer {seu-token}
```

**Resposta:**

```json
{
  "message": "Article restored to version 2 successfully",
  "data": {
    "_id": "507f1f77bcf86cd799439011",
    "title": "Introdução ao Laravel 12",
    "version_number": 4,
    "restored_from_version": 2
  }
}
```

#### 🔄 Comparar Versões

```bash
POST /api/articles/{id}/versions/compare
Authorization: Bearer {seu-token}
Content-Type: application/json
```

**Body:**

```json
{
  "version1_id": "507f1f77bcf86cd799439012",
  "version2_id": "507f1f77bcf86cd799439013"
}
```

**Resposta:**

```json
{
  "comparison": {
    "title": {
      "changed": true,
      "old": "Introdução ao Laravel",
      "new": "Introdução ao Laravel 12"
    },
    "content": {
      "changed": true,
      "old": "Laravel é um framework...",
      "new": "Laravel 12 traz novidades..."
    },
    "status": {
      "changed": false,
      "value": "published"
    }
  }
}
```

### Desabilitar Versionamento Temporariamente

```php
// Para updates sem criar versão (ex: contadores)
$article->withoutVersioning(function ($article) {
    $article->increment('views_count');
});
```

</details>

<details>
<summary><strong>🐳 Docker - Configuração Detalhada</strong></summary>
<br>

### Serviços

**knowledge-hub** - Aplicação Laravel

- Porta: 8004
- PHP 8.4
- Composer
- Artisan CLI

**mongo** - MongoDB

- Porta: 27017
- Versão: 6.0
- Volume persistente

### docker-compose.yml

```yaml
services:
  knowledge-hub:
    build: .
    ports:
      - "8004:8004"
    depends_on:
      - mongo
    volumes:
      - .:/var/www/html
    
  mongo:
    image: mongo:6.0
    ports:
      - "27017:27017"
    volumes:
      - mongo_data:/data/db
    environment:
      MONGO_INITDB_DATABASE: knowledge_hub

volumes:
  mongo_data:
```

### Comandos Úteis

```bash
# Ver logs
docker logs knowledge-hub-knowledge-hub-1 -f
docker logs knowledge-hub-mongo-1 -f

# Restart
docker-compose restart

# Down e Up
docker-compose down
docker-compose up -d

# Rebuild completo
docker-compose down -v
docker-compose up -d --build
```

</details>

---

**Desenvolvido com ❤️ usando Laravel e MongoDB**