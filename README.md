# Knowledge Hub API

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![MongoDB](https://img.shields.io/badge/MongoDB-6.0-47A248?style=flat-square&logo=mongodb&logoColor=white)](https://mongodb.com)
[![Redis](https://img.shields.io/badge/Redis-7.0-DC382D?style=flat-square&logo=redis&logoColor=white)](https://redis.io)
[![Pest](https://img.shields.io/badge/Pest-4.1-8BC34A?style=flat-square&logo=pest&logoColor=white)](https://pestphp.com)
[![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?style=flat-square&logo=docker&logoColor=white)](https://docker.com)

> API RESTful moderna para gerenciamento de conhecimento com suporte a artigos versionados, autenticação segura, MongoDB e Redis.

## 📖 Sobre o Projeto

Knowledge Hub é uma API robusta desenvolvida com Laravel 12 e MongoDB, projetada para gerenciar conteúdo de conhecimento de forma eficiente e escalável. O projeto implementa padrões modernos de arquitetura, incluindo DTOs, Value Objects, Repository Pattern e Service Layer.

### Principais Funcionalidades

- 🔐 **Autenticação JWT** - Sistema completo com Laravel Sanctum
- 📝 **Gerenciamento de Artigos** - CRUD completo com suporte a múltiplos tipos
- � **Sistema de Comentários** - Comentários aninhados com edição e exclusão
- ❤️ **Sistema de Likes** - Curtir/descurtir artigos com contadores automáticos
- 👥 **Sistema de Seguidores** - Seguir usuários e feed personalizado
- 📰 **Feed Inteligente** - Feed público e personalizado baseado em seguidos
- 👤 **Perfis Públicos** - Perfis de usuário com limitação para visitantes
- �🕐 **Versionamento Automático** - Histórico completo de alterações em artigos
- 🔄 **Restauração de Versões** - Volte para qualquer versão anterior
- 📊 **Comparação de Versões** - Visualize diferenças entre versões
- 📈 **Ranking em Tempo Real** - Redis Sorted Sets para artigos mais acessados
- 🎯 **Rastreamento de Visualizações** - Tracking automático de acessos
- 🏷️ **Tags e Categorias** - Organização flexível de conteúdo
- 🎯 **SEO Otimizado** - Metadados completos para otimização
- ⚡ **Performance** - Cache Redis, índices e queries otimizadas
- 🧪 **100% Testado** - Cobertura completa com Pest
- 🐳 **Docker Ready** - Ambiente containerizado

## 🚀 Tecnologias

### Backend

- **Laravel 12.0** - Framework PHP moderno
- **PHP 8.4** - Última versão com recursos avançados
- **MongoDB 6.0** - Banco de dados NoSQL flexível
- **Redis 7.0** - Cache e ranking em tempo real
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
docker exec -it knowledge-hub-app composer install
```

### 5. Gere a chave da aplicação

```bash
docker exec -it knowledge-hub-app php artisan key:generate
```

### 6. Execute as migrations

```bash
docker exec -it knowledge-hub-app php artisan migrate
```

### 7. Acesse a aplicação

```text
http://localhost:8004/api
```

## 🔑 Autenticação

A API utiliza Laravel Sanctum para autenticação via tokens Bearer.

### Endpoints Principais

```bash
# Autenticação
POST /api/register
POST /api/login
POST /api/logout
POST /api/revoke-all

# Perfil
GET /api/user
GET /api/users/{id}  # Perfil público (limitado para visitantes)
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

# Listar artigos populares (cache de 1 hora)
GET /api/articles/popular?limit=10&days=30

# Ranking em tempo real (Redis Sorted Sets)
GET /api/articles/ranking?limit=10

# Estatísticas do ranking
GET /api/articles/ranking/statistics

# Criar artigo
POST /api/articles

# Visualizar artigo (rastreia visualização automaticamente)
GET /api/articles/{id}

# Informações de ranking de um artigo (autenticado)
GET /api/articles/{id}/ranking

# Atualizar artigo (cria versão automaticamente)
PUT /api/articles/{id}

# Deletar artigo
DELETE /api/articles/{id}

# Sincronizar ranking do banco para Redis (autenticado)
POST /api/articles/ranking/sync

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

## 💬 Sistema de Comentários

Sistema completo de comentários em artigos com contadores automáticos.

### Recursos de Comentários

- **CRUD Completo**: Criar, editar, excluir e listar comentários
- **Contadores Automáticos**: Atualiza `comment_count` via Observer
- **Validação de Propriedade**: Apenas o autor pode editar/excluir
- **Rate Limiting**: 30 comentários por minuto
- **Soft Deletes**: Comentários excluídos podem ser restaurados

### Endpoints de Comentários

```bash
# Listar comentários de um artigo
GET /api/articles/{articleId}/comments

# Criar comentário
POST /api/articles/{articleId}/comments

# Atualizar comentário (apenas autor)
PUT /api/comments/{id}

# Deletar comentário (apenas autor)
DELETE /api/comments/{id}
```

**Rate Limiting:** 30 comentários/minuto por usuário

## ❤️ Sistema de Likes

Sistema de curtidas em artigos com toggle automático.

### Recursos de Likes

- **Toggle Inteligente**: Curtir/descurtir em um único endpoint
- **Contadores Automáticos**: Atualiza `like_count` via Observer
- **Verificação de Status**: Checar se usuário já curtiu
- **Rate Limiting**: 60 likes por minuto
- **Constraint Único**: Um like por usuário por artigo

### Endpoints de Likes

```bash
# Curtir/Descurtir artigo (toggle)
POST /api/articles/{articleId}/like

# Verificar se usuário curtiu
GET /api/articles/{articleId}/like/check
```

**Rate Limiting:** 60 likes/minuto por usuário

## 👥 Sistema de Seguidores

Sistema completo de relacionamentos entre usuários.

### Recursos de Seguidores

- **Seguir/Deixar de Seguir**: Toggle em um único endpoint
- **Prevenção de Auto-follow**: Usuário não pode seguir a si mesmo
- **Listagem**: Seguidores e seguindo com paginação
- **Verificação de Status**: Checar se usuário segue outro
- **Rate Limiting**: 30 ações por minuto

### Endpoints de Seguidores

```bash
# Seguir/Deixar de seguir usuário (toggle)
POST /api/users/{userId}/follow

# Listar seguidores de um usuário
GET /api/users/{userId}/followers

# Listar quem o usuário segue
GET /api/users/{userId}/following

# Verificar se está seguindo
GET /api/users/{userId}/follow/check
```

**Rate Limiting:** 30 ações/minuto por usuário

## 📰 Sistema de Feed

Feed inteligente com artigos públicos e personalizados.

### Recursos de Feed

- **Feed Público**: Artigos mais populares baseado em score ponderado
- **Feed Personalizado**: Prioriza artigos de usuários seguidos
- **Algoritmo de Score**: `(view_count * 0.4) + (like_count * 0.4) + (comment_count * 0.2)`
- **Bônus de Prioridade**: Artigos de seguidos ganham +10000 no score
- **Paginação**: Suporte completo para navegação

### Endpoints de Feed

```bash
# Feed público (para todos)
GET /api/feed

# Feed personalizado (autenticado)
GET /api/feed/personalized
```

## 👤 Perfis de Usuário

Perfis públicos com limitações para visitantes não autenticados.

### Recursos de Perfil

- **Perfil Completo**: Nome, username, bio, avatar, estatísticas
- **Limitação de Visitantes**: Não autenticados veem apenas 10 artigos
- **Estatísticas**: Contadores de seguidores e seguindo
- **Status de Relacionamento**: Indica se usuário autenticado está seguindo
- **Artigos do Usuário**: Listagem paginada de artigos publicados

### Endpoint de Perfil

```bash
# Visualizar perfil público
GET /api/users/{id}
```

**Limitação:** Visitantes não autenticados veem apenas 10 artigos mais recentes.

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
docker exec -it knowledge-hub-app ./vendor/bin/pest

# Testes específicos
docker exec -it knowledge-hub-app ./vendor/bin/pest tests/Unit/ArticleVersioningTest.php

# Com cobertura
docker exec -it knowledge-hub-app ./vendor/bin/pest --coverage
```

### Estatísticas

- ✅ **860 testes** passando
- ✅ **100% cobertura** em todos os componentes
- ✅ Testes unitários e de integração
- ✅ 2.157 assertions

### Scripts de Demonstração

```bash
# Testar versionamento manualmente
docker exec -it knowledge-hub-app php test-versioning.php
```

## 🗄️ Banco de Dados

### Collections MongoDB

| Collection | Descrição |
|-----------|-----------|
| `users` | Usuários do sistema |
| `articles` | Artigos com versionamento |
| `article_versions` | Histórico de versões |
| `comments` | Comentários em artigos |
| `likes` | Curtidas em artigos |
| `followers` | Relacionamentos entre usuários |
| `personal_access_tokens` | Tokens Sanctum |

### Acessar Dados

```bash
# Via mongosh
docker exec -it knowledge-hub-mongo mongosh
use knowledge_hub
db.articles.find().pretty()

# Via Laravel Tinker
docker exec -it knowledge-hub-app php artisan tinker
Article::all()
ArticleVersion::all()
```

## 🔧 Desenvolvimento

### Comandos Úteis

```bash
# Acessar container
docker exec -it knowledge-hub-app bash

# Logs
docker logs knowledge-hub-app -f

# Rebuild
docker-compose down && docker-compose up -d --build

# Análise estática
docker exec -it knowledge-hub-app ./vendor/bin/phpstan analyse

# Code style
docker exec -it knowledge-hub-app ./vendor/bin/pint
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

### [3.0.0] - 2025-11-20

#### ✨ Adicionado

- **Sistema de Comentários**
  - CRUD completo de comentários em artigos
  - Atualização automática de `comment_count` via Observer
  - Validação de propriedade (apenas autor pode editar/excluir)
  - Rate limiting (30/min)
  - Soft deletes

- **Sistema de Likes**
  - Toggle curtir/descurtir em endpoint único
  - Atualização automática de `like_count` via Observer
  - Verificação de status de like
  - Constraint único (um like por usuário/artigo)
  - Rate limiting (60/min)

- **Sistema de Seguidores**
  - Seguir/deixar de seguir usuários
  - Prevenção de auto-follow
  - Listagem de seguidores e seguindo
  - Verificação de relacionamento
  - Rate limiting (30/min)

- **Sistema de Feed**
  - Feed público com score ponderado
  - Feed personalizado priorizando seguidos
  - Algoritmo: `(views * 0.4) + (likes * 0.4) + (comments * 0.2)`
  - Bônus de +10000 para artigos de seguidos

- **Perfis Públicos**
  - Endpoint de perfil de usuário
  - Limitação de 10 artigos para visitantes não autenticados
  - Estatísticas de seguidores
  - Status de relacionamento (is_following)

- **Arquitetura**
  - Separação de Repositories e Services em providers distintos
  - RepositoryServiceProvider para bindings de repositórios
  - BusinessServiceProvider para bindings de serviços
  - Uso de JsonResponse::HTTP_* constants
  - FeedRepository para separação de queries

#### 🔧 Melhorado

- AppServiceProvider simplificado (apenas cache e observers)
- Separação de concerns entre Service e Repository layers
- Code quality (PHPStan level 10 zerado)
- Formatação consistente com Laravel Pint
- Testes completos para todas as novas funcionalidades

### [2.1.0] - 2025-11-17

#### ✨ Adicionado

- Sistema de ranking em tempo real com Redis Sorted Sets
- Rastreamento automático de visualizações de artigos
- Endpoint público de ranking (`GET /api/articles/ranking`)
- Endpoint de estatísticas do ranking
- Endpoint para informações de ranking individual
- Comando Artisan para sincronização do ranking (`articles:sync-ranking`)
- Middleware `TrackArticleView` para rastreamento automático
- Service `ArticleRankingService` com operações de ranking
- Testes completos de ranking (Unit + Feature)
- Método `withoutVersioning()` no trait Versionable
- Documentação completa do sistema de ranking

#### 🔧 Melhorado

- Performance de consultas de artigos populares
- Sistema de cache otimizado com Redis
- Cobertura de testes mantida em 100%

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

#### 🔥 Listar Artigos Populares (com Cache)

Endpoint público para recuperar os artigos mais populares baseados em visualizações. Os resultados são automaticamente cacheados por **1 hora** para melhor performance.

```bash
GET /api/articles/popular?limit=10&days=30
```

**Query Parameters:**

| Parâmetro | Tipo | Padrão | Descrição |
|-----------|------|--------|-----------|
| `limit` | integer | 10 | Número máximo de artigos a retornar (1-100) |
| `days` | integer | 30 | Período em dias para considerar artigos recentes (1-365) |

**Exemplo de Requisição:**

```bash
# Top 5 artigos dos últimos 7 dias
GET /api/articles/popular?limit=5&days=7

# Top 20 artigos do último mês
GET /api/articles/popular?limit=20&days=30
```

**Resposta:**

```json
{
  "data": [
    {
      "_id": "507f1f77bcf86cd799439011",
      "title": "Guia Completo de Docker",
      "slug": "guia-completo-de-docker",
      "type": "tutorial",
      "status": "published",
      "author_id": "507f191e810c19729de860ea",
      "excerpt": "Aprenda Docker do zero ao avançado...",
      "reading_time": 15,
      "view_count": 1523,
      "published_at": "2025-01-01T08:00:00Z",
      "created_at": "2025-01-01T08:00:00Z",
      "updated_at": "2025-01-04T10:00:00Z"
    },
    {
      "_id": "507f1f77bcf86cd799439012",
      "title": "Introdução ao MongoDB",
      "slug": "introducao-ao-mongodb",
      "type": "article",
      "status": "published",
      "view_count": 987,
      "published_at": "2025-01-03T14:00:00Z"
    }
  ]
}
```

**Características:**

- ✅ **Cache Inteligente**: Resultados armazenados em Redis por 1 hora
- ✅ **Invalidação Automática**: Cache atualizado quando artigos são criados, atualizados ou deletados
- ✅ **Performance**: Queries otimizadas com índices MongoDB
- ✅ **Filtros**: Apenas artigos publicados (status='published')
- ✅ **Ordenação**: Classificado por `view_count` (decrescente)
- ✅ **Período Configurável**: Filtra por `published_at` >= (hoje - N dias)

**Implementação Técnica:**

```php
// Caminho do cache Redis
Cache Key: "popular_articles:days:30:limit:10"

// Invalidação automática via Observer
ArticleObserver → created/updated/deleted/restored
  → CacheInvalidator→invalidatePopularArticlesCache()
  → Redis: DELETE "popular_articles:*"
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
<summary><strong>� Ranking em Tempo Real - Detalhes</strong></summary>
<br>

### Visão Geral

O sistema de ranking utiliza **Redis Sorted Sets** para rastrear e rankear artigos mais acessados em tempo real, oferecendo performance extrema e dados sempre atualizados.

### Características

- ⚡ **Performance**: Consultas em O(log N) com Redis Sorted Sets
- 🔄 **Tempo Real**: Atualização instantânea a cada visualização
- 📊 **Estatísticas**: Métricas agregadas do ranking
- 🔌 **Auto-sync**: Sincronização automática com MongoDB
- ⏱️ **TTL**: Expiração automática de 90 dias
- 🎯 **Tracking Automático**: Middleware rastreia visualizações

### Endpoints Detalhados

#### 📊 Obter Ranking em Tempo Real

```bash
GET /api/articles/ranking?limit=10
```

**Parâmetros:**
- `limit` (opcional): Número de artigos (padrão: 10, máx: 100)

**Resposta:**

```json
{
  "data": [
    {
      "rank": 1,
      "article_id": "507f1f77bcf86cd799439011",
      "views": 1523,
      "article": {
        "title": "Introdução ao Laravel 12",
        "slug": "introducao-ao-laravel-12",
        "excerpt": "Aprenda os fundamentos...",
        "author_id": "507f191e810c19729de860ea",
        "published_at": "2025-01-04T10:00:00Z"
      }
    },
    {
      "rank": 2,
      "article_id": "507f1f77bcf86cd799439012",
      "views": 987,
      "article": {
        "title": "MongoDB com Laravel",
        "slug": "mongodb-com-laravel",
        "excerpt": "Integração completa...",
        "author_id": "507f191e810c19729de860ea",
        "published_at": "2025-01-05T14:30:00Z"
      }
    }
  ]
}
```

#### 📈 Estatísticas do Ranking

```bash
GET /api/articles/ranking/statistics
```

**Resposta:**

```json
{
  "data": {
    "total_articles": 45,
    "total_views": 12547.0,
    "top_score": 1523.0
  }
}
```

#### 🔍 Informações de Ranking de um Artigo

```bash
GET /api/articles/{id}/ranking
Authorization: Bearer {seu-token}
```

**Resposta:**

```json
{
  "data": {
    "article_id": "507f1f77bcf86cd799439011",
    "rank": 1,
    "views": 1523,
    "article": {
      "title": "Introdução ao Laravel 12",
      "slug": "introducao-ao-laravel-12",
      "view_count": 1523
    }
  }
}
```

#### 🔄 Sincronizar Ranking do Banco de Dados

```bash
POST /api/articles/ranking/sync
Authorization: Bearer {seu-token}
```

**Resposta:**

```json
{
  "message": "Ranking sincronizado com sucesso."
}
```

### Rastreamento Automático

O sistema rastreia visualizações automaticamente quando um artigo é acessado via `GET /api/articles/{id}`:

```bash
# Cada acesso incrementa:
# 1. Redis Sorted Set (ranking em tempo real)
# 2. MongoDB view_count (backup persistente)

GET /api/articles/507f1f77bcf86cd799439011
```

**Comportamento:**
- ✅ Incrementa score no Redis instantaneamente
- ✅ Atualiza `view_count` no MongoDB sem criar versão
- ✅ Não afeta performance (operações assíncronas)

### Comando Artisan

```bash
# Sincronizar ranking via CLI
docker exec -it knowledge-hub-app php artisan articles:sync-ranking
```

**Output:**

```text
Sincronizando ranking de artigos...
✓ Ranking sincronizado com sucesso!

┌────────────────────────┬────────┐
│ Métrica                │ Valor  │
├────────────────────────┼────────┤
│ Total de artigos       │ 45     │
│ Total de visualizações │ 12,547 │
│ Maior pontuação        │ 1,523  │
└────────────────────────┴────────┘
```

### Implementação Técnica

#### Redis Sorted Set

```php
// Estrutura no Redis
ZADD articles:ranking:views 1523 "507f1f77bcf86cd799439011"
ZADD articles:ranking:views 987 "507f1f77bcf86cd799439012"
ZADD articles:ranking:views 654 "507f1f77bcf86cd799439013"

// Consulta top 10
ZREVRANGE articles:ranking:views 0 9 WITHSCORES
```

#### Service Layer

```php
// Incrementar visualização
$rankingService->incrementView($articleId);

// Obter ranking
$topArticles = $rankingService->getTopArticles(10);

// Obter posição
$rank = $rankingService->getArticleRank($articleId);

// Obter score
$views = $rankingService->getArticleScore($articleId);
```

### Casos de Uso

1. **Homepage**: Exibir artigos em alta
2. **Sidebar**: Widget de "Mais Lidos"
3. **Analytics**: Dashboard de performance
4. **Recomendações**: Sugerir conteúdo popular
5. **Trending**: Identificar tendências

### Performance

- 📊 **Consulta**: < 1ms para top 100
- 🔄 **Atualização**: < 0.5ms por incremento
- 💾 **Memória**: ~100 bytes por artigo
- ⚡ **Throughput**: > 10k req/s

### Manutenção

```php
// Resetar ranking
$rankingService->resetRanking();

// Remover artigo específico
$rankingService->removeArticle($articleId);

// Sincronizar do banco
$rankingService->syncFromDatabase();

// Obter estatísticas
$stats = $rankingService->getStatistics();
```

</details>

<details>
<summary><strong>�🕐 Sistema de Versionamento - Detalhes</strong></summary>
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
docker logs knowledge-hub-app -f
docker logs knowledge-hub-mongo -f

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