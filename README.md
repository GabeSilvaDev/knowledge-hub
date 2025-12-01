# Knowledge Hub API

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![MongoDB](https://img.shields.io/badge/MongoDB-6.0-47A248?style=flat-square&logo=mongodb&logoColor=white)](https://mongodb.com)
[![Redis](https://img.shields.io/badge/Redis-7.0-DC382D?style=flat-square&logo=redis&logoColor=white)](https://redis.io)
[![Meilisearch](https://img.shields.io/badge/Meilisearch-1.12-FF5CAA?style=flat-square&logo=meilisearch&logoColor=white)](https://meilisearch.com)
[![Neo4j](https://img.shields.io/badge/Neo4j-5.13-008CC1?style=flat-square&logo=neo4j&logoColor=white)](https://neo4j.com)
[![Pest](https://img.shields.io/badge/Pest-4.1-8BC34A?style=flat-square&logo=pest&logoColor=white)](https://pestphp.com)
[![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?style=flat-square&logo=docker&logoColor=white)](https://docker.com)

<p align="center">
  <a href="#-about-the-project">🇺🇸 English</a> •
  <a href="#-sobre-o-projeto">🇧🇷 Português</a>
</p>

---

# 🇺🇸 English

> Modern RESTful API for knowledge management with support for versioned articles, secure authentication, MongoDB, and Redis.

## 📖 About the Project

Knowledge Hub is a robust API built with Laravel 12 and MongoDB, designed to manage knowledge content efficiently and at scale. The project implements modern architectural patterns, including DTOs, Value Objects, Repository Pattern, and Service Layer.

### Key Features

- 🔐 **JWT Authentication** - Complete system with Laravel Sanctum
- 📝 **Article Management** - Full CRUD with support for multiple types
- 💬 **Comment System** - Nested comments with editing and deletion
- ❤️ **Like System** - Like/unlike articles with automatic counters
- 👥 **Follower System** - Follow users and personalized feed
- 📰 **Smart Feed** - Public and personalized feed based on followed users
- 👤 **Public Profiles** - User profiles with limited access for visitors
- 🕐 **Automatic Versioning** - Complete history of article changes
- 🔄 **Version Restoration** - Revert to any previous version
- 📊 **Version Comparison** - View differences between versions
- 📈 **Real-Time Ranking** - Redis Sorted Sets for articles and users
- 🏆 **Influence Ranking** - Weighted score of influential users
- 🔍 **Advanced Search** - Meilisearch with autocomplete and filters
- 🤖 **Neo4j Recommendations** - Relationship graph for smart suggestions
- 🎯 **View Tracking** - Automatic access tracking
- 🏷️ **Tags and Categories** - Flexible content organization
- 🎯 **SEO Optimized** - Complete metadata for optimization
- ⚡ **Performance** - Redis cache, indexes, and optimized queries
- 🧪 **100% Tested** - Complete coverage with Pest
- 🐳 **Docker Ready** - Containerized environment

## 🚀 Technologies

### Backend

- **Laravel 12.0** - Modern PHP Framework
- **PHP 8.4** - Latest version with advanced features
- **MongoDB 6.0** - Flexible NoSQL database
- **Redis 7.0** - Cache and real-time ranking
- **Neo4j 5.13** - Graph database for recommendations
- **Meilisearch 1.12** - Full-text search engine
- **Laravel Sanctum 4.2** - API Authentication
- **Laravel Scout 10.x** - Search integration

### Development

- **Pest 4.1** - Modern testing framework
- **PHPStan** - Static code analysis
- **Laravel Pint** - Automatic code style
- **Docker & Docker Compose** - Containerization

### Architecture

- **Repository Pattern** - Data access abstraction
- **Service Layer** - Isolated business logic
- **DTOs** - Typed data transfer
- **Value Objects** - Immutable value objects
- **Enums** - Enumerated types for states

## 📋 Prerequisites

- Docker & Docker Compose
- Git
- Make (optional)

## ⚡ Quick Start

### 1. Clone the repository

```bash
git clone <repository-url>
cd knowledge-hub
```

### 2. Configure the environment

```bash
cp .env.example .env
```

### 3. Start the containers

```bash
docker-compose up -d
```

### 4. Install dependencies

```bash
docker exec -it knowledge-hub-app composer install
```

### 5. Generate the application key

```bash
docker exec -it knowledge-hub-app php artisan key:generate
```

### 6. Run migrations

```bash
docker exec -it knowledge-hub-app php artisan migrate
```

### 7. Access the application

```text
http://localhost:8004/api
```

## 🔑 Authentication

Complete authentication system with Laravel Sanctum using Bearer tokens. Supports registration, login, logout, and token revocation with enterprise-level security.

## 📝 Article Management

Robust content management system with automatic versioning and complete change history.

### Main Features

- **Full CRUD** - Complete create, read, update, and delete operations
- **Automatic Versioning** - History of all changes with restoration capability
- **Multiple Types** - Articles, tutorials, guides, and technical documentation
- **Status Management** - Draft, published, archived with controlled transitions
- **Integrated SEO** - Meta tags, friendly slugs, and automatic optimization
- **Reading Time** - Automatic calculation based on word count
- **Soft Deletes** - Safe deletion with recovery possibility

## 💬 Comment System

Rich interaction on articles with complete comment system and integrated moderation.

### Main Features

- **Full CRUD** - Create, edit, delete, and list comments
- **Automatic Counters** - Real-time updates via observers
- **Ownership Control** - Only authors can modify their comments
- **Rate Limiting** - Spam protection (30 comments/minute)
- **Soft Deletes** - Recovery of deleted comments

## ❤️ Like System

Simplified engagement with smart and efficient like system.

### Main Features

- **Automatic Toggle** - Like/unlike in single endpoint
- **Real-Time Counters** - Instant updates via observers
- **Status Check** - Check user's like status
- **Rate Limiting** - Abuse protection (60 likes/minute)
- **Unique Constraint** - One like per user per article

## 👥 Follower System

Integrated social network with user relationships and community building.

### Main Features

- **Relationship Toggle** - Follow/unfollow in single endpoint
- **Security Validations** - Self-follow and duplication prevention
- **Complete Listings** - Followers and following with pagination
- **Status Check** - Relationship checks between users
- **Rate Limiting** - Automation protection (30 actions/minute)

## 📰 Smart Feed System

Optimized content discovery with recommendation algorithms and personalization.

### Main Features

- **Public Feed** - Most popular articles with weighted score
- **Personalized Feed** - Content prioritization from followed users
- **Smart Algorithm** - Scoring based on views, likes, and comments
- **Social Bonus** - Articles from followed users receive priority boost
- **Optimized Pagination** - Efficient navigation in large volumes

## 👤 Public Profiles

Complete profile pages with information, statistics, and privacy control.

### Main Features

- **Rich Profiles** - Name, username, bio, avatar, and complete statistics
- **Access Control** - Unauthenticated visitors with limited view
- **Social Metrics** - Follower, following, and article counters
- **Relationship Status** - Visual indication of existing connections
- **Content Portfolio** - Paginated listing of published articles

## 🏗️ Architecture

### Folder Structure

```text
app/
├── Contracts/           # Interfaces
├── DTOs/               # Data Transfer Objects
├── Enums/              # Enumerations
├── Exceptions/         # Custom exceptions
├── Helpers/            # Helper functions
├── Http/
│   ├── Controllers/    # API Controllers
│   └── Requests/       # Form Requests
├── Models/             # Eloquent/MongoDB Models
├── Repositories/       # Data layer
├── Services/           # Business logic
├── Traits/             # Reusable traits
└── ValueObjects/       # Value objects
```

### Implemented Patterns

- **Repository Pattern** - Data access abstraction
- **Service Layer** - Isolated business logic
- **DTO Pattern** - Typed data transfer
- **Value Objects** - Value encapsulation
- **Traits** - Reusable behaviors (e.g., Versionable)

## 📊 Ranking System

Real-time analytics with content ranking, users, and engagement metrics.

### Article Ranking

- **Top Articles** - Most viewed content in real-time
- **Redis Sorted Sets** - Optimized performance for rankings
- **General Statistics** - Aggregated article metrics
- **Automatic Tracking** - Dedicated middleware for accurate counting

### Influential User Ranking

Ranking system that calculates each user's influence based on multiple factors.

- **Influence Formula** - Weighted score by followers, views, likes, comments, and articles
- **Top Users** - List of most influential platform users
- **Individual Ranking** - Position and detailed breakdown for each user
- **Synchronization** - Artisan command and endpoint for ranking updates
- **Redis Sorted Sets** - Instant queries with high performance

#### Calculation Formula

```
Score = (Followers × 2.0) + (Views × 0.5) + (Likes × 1.0) + (Comments × 0.8) + (Articles × 1.5)
```

## 🔍 Advanced Search System

Full-text search engine with Meilisearch for fast and accurate content discovery.

### Main Features

- **Full-Text Search** - Search in title, content, tags, and author
- **Smart Autocomplete** - Real-time suggestions while typing
- **Advanced Filters** - Status, type, tags, categories, and dates
- **Error Tolerance** - Native typo-tolerance from Meilisearch
- **Ultra-Fast Performance** - Responses in milliseconds
- **Highlighting** - Highlighting of found terms

## 🤖 Recommendation System

Intelligent recommendation engine with Neo4j for content discovery and relevant connections.

### Main Features

- **Similar Users** - Suggestions based on common followers
- **Related Articles** - Recommendations by shared tags and categories
- **Influential Authors** - Discovery of popular content creators
- **Topics of Interest** - Identification of areas based on interactions
- **Automatic Synchronization** - Observers keep Neo4j updated in real-time
- **Graceful Degradation** - System works even if Neo4j is unavailable

## 🗄️ Database

### MongoDB Structure

| Collection | Description |
|-----------|-----------|
| `users` | Users and authentication |
| `articles` | Articles and metadata |
| `article_versions` | Version history |
| `comments` | Comments and interactions |
| `likes` | User likes |
| `followers` | Social network and relationships |
| `personal_access_tokens` | Sanctum access tokens |

## 🔧 Code Quality

### Tools and Standards

- **Pest 4.1** - Modern testing framework with 100% coverage
- **PHPStan Level 10** - Maximum static analysis with no errors
- **Laravel Pint** - Automatic code style following PSR-12
- **Rector** - Automatic refactoring and code modernization

### Tests

Complete coverage including unit, integration, and feature tests for all critical system functionalities.

## 🤝 Contributing

Contributions are welcome! Follow the established code standards, maintain 100% test coverage, and run all quality checks before submitting pull requests.

## 🚀 Deploy and Production

Containerized system with Docker, ready for deployment in any environment that supports containers. Configurations optimized for high performance and horizontal scalability.

## 📊 Performance

- **Redis Cache** - Query optimization and database load reduction
- **MongoDB Indexes** - Optimized queries for high performance
- **Rate Limiting** - Abuse protection and availability guarantee
- **Lazy Loading** - Relationships loaded on demand
- **Query Optimization** - Spatie Query Builder for efficient filtering

## 🔒 Security

- **Robust Authentication** - Laravel Sanctum with secure tokens
- **Password Hashing** - Bcrypt for maximum security
- **Rigorous Validation** - Form Requests on all endpoints
- **Rate Limiting** - Protection against brute force and DDoS
- **Soft Deletes** - Critical data recovery

## 📄 License

This project is under the MIT license.

---

# 🇧🇷 Português

> API RESTful moderna para gerenciamento de conhecimento com suporte a artigos versionados, autenticação segura, MongoDB e Redis.

## 📖 Sobre o Projeto

Knowledge Hub é uma API robusta desenvolvida com Laravel 12 e MongoDB, projetada para gerenciar conteúdo de conhecimento de forma eficiente e escalável. O projeto implementa padrões modernos de arquitetura, incluindo DTOs, Value Objects, Repository Pattern e Service Layer.

### Principais Funcionalidades

- 🔐 **Autenticação JWT** - Sistema completo com Laravel Sanctum
- 📝 **Gerenciamento de Artigos** - CRUD completo com suporte a múltiplos tipos
- 💬 **Sistema de Comentários** - Comentários aninhados com edição e exclusão
- ❤️ **Sistema de Likes** - Curtir/descurtir artigos com contadores automáticos
- 👥 **Sistema de Seguidores** - Seguir usuários e feed personalizado
- 📰 **Feed Inteligente** - Feed público e personalizado baseado em seguidos
- 👤 **Perfis Públicos** - Perfis de usuário com limitação para visitantes
- 🕐 **Versionamento Automático** - Histórico completo de alterações em artigos
- 🔄 **Restauração de Versões** - Volte para qualquer versão anterior
- 📊 **Comparação de Versões** - Visualize diferenças entre versões
- 📈 **Ranking em Tempo Real** - Redis Sorted Sets para artigos e usuários
- 🏆 **Ranking de Influência** - Score ponderado de usuários influentes
- 🔍 **Busca Avançada** - Meilisearch com autocomplete e filtros
- 🤖 **Recomendações Neo4j** - Grafo de relacionamentos para sugestões inteligentes
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
- **Neo4j 5.13** - Banco de dados de grafos para recomendações
- **Meilisearch 1.12** - Motor de busca full-text
- **Laravel Sanctum 4.2** - Autenticação API
- **Laravel Scout 10.x** - Integração de busca

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

Sistema de autenticação completo com Laravel Sanctum utilizando tokens Bearer. Suporta registro, login, logout e revogação de tokens com segurança de nível empresarial.

## 📝 Gerenciamento de Artigos

Sistema robusto de gerenciamento de conteúdo com versionamento automático e histórico completo de alterações.

### Recursos Principais

- **CRUD Completo** - Operações completas de criação, leitura, atualização e exclusão
- **Versionamento Automático** - Histórico de todas as alterações com possibilidade de restauração
- **Múltiplos Tipos** - Artigos, tutoriais, guias e documentação técnica
- **Gestão de Status** - Draft, publicado, arquivado com transições controladas
- **SEO Integrado** - Meta tags, slugs amigáveis e otimização automática
- **Tempo de Leitura** - Cálculo automático baseado em contagem de palavras
- **Soft Deletes** - Exclusão segura com possibilidade de recuperação

## 💬 Sistema de Comentários

Interação rica em artigos com sistema de comentários completo e moderação integrada.

### Recursos Principais

- **CRUD Completo** - Criar, editar, excluir e listar comentários
- **Contadores Automáticos** - Atualização em tempo real via observers
- **Controle de Propriedade** - Apenas autores podem modificar seus comentários
- **Rate Limiting** - Proteção contra spam (30 comentários/minuto)
- **Soft Deletes** - Recuperação de comentários excluídos

## ❤️ Sistema de Likes

Engajamento simplificado com sistema de curtidas inteligente e eficiente.

### Recursos Principais

- **Toggle Automático** - Curtir/descurtir em único endpoint
- **Contadores em Tempo Real** - Atualização instantânea via observers
- **Verificação de Status** - Checar estado de curtida do usuário
- **Rate Limiting** - Proteção contra abuso (60 likes/minuto)
- **Constraint Único** - Uma curtida por usuário por artigo

## 👥 Sistema de Seguidores

Rede social integrada com relacionamentos entre usuários e construção de comunidade.

### Recursos Principais

- **Toggle de Relacionamento** - Seguir/deixar de seguir em endpoint único
- **Validações de Segurança** - Prevenção de auto-follow e duplicações
- **Listagens Completas** - Seguidores e seguindo com paginação
- **Verificação de Status** - Checagem de relacionamentos entre usuários
- **Rate Limiting** - Proteção contra automação (30 ações/minuto)

## 📰 Sistema de Feed Inteligente

Descoberta de conteúdo otimizada com algoritmos de recomendação e personalização.

### Recursos Principais

- **Feed Público** - Artigos mais populares com score ponderado
- **Feed Personalizado** - Priorização de conteúdo de usuários seguidos
- **Algoritmo Inteligente** - Pontuação baseada em views, likes e comentários
- **Bônus Social** - Artigos de seguidos recebem boost de prioridade
- **Paginação Otimizada** - Navegação eficiente em grandes volumes

## 👤 Perfis Públicos

Páginas de perfil completas com informações, estatísticas e controle de privacidade.

### Recursos Principais

- **Perfis Ricos** - Nome, username, bio, avatar e estatísticas completas
- **Controle de Acesso** - Visitantes não autenticados com visualização limitada
- **Métricas Sociais** - Contadores de seguidores, seguindo e artigos
- **Status de Relacionamento** - Indicação visual de conexões existentes
- **Portfólio de Conteúdo** - Listagem paginada de artigos publicados

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

## 📊 Sistema de Ranking

Analytics em tempo real com ranking de conteúdo, usuários e métricas de engajamento.

### Ranking de Artigos

- **Top Artigos** - Conteúdos mais visualizados em tempo real
- **Redis Sorted Sets** - Performance otimizada para rankings
- **Estatísticas Gerais** - Métricas agregadas de artigos
- **Tracking Automático** - Middleware dedicado para contagem precisa

### Ranking de Usuários Influentes

Sistema de ranking que calcula a influência de cada usuário baseado em múltiplos fatores.

- **Fórmula de Influência** - Score ponderado por seguidores, views, likes, comentários e artigos
- **Top Usuários** - Listagem dos usuários mais influentes da plataforma
- **Ranking Individual** - Posição e breakdown detalhado de cada usuário
- **Sincronização** - Command artisan e endpoint para atualização do ranking
- **Redis Sorted Sets** - Consultas instantâneas com alta performance

#### Fórmula de Cálculo

```
Score = (Seguidores × 2.0) + (Views × 0.5) + (Likes × 1.0) + (Comentários × 0.8) + (Artigos × 1.5)
```

## 🔍 Sistema de Busca Avançada

Motor de busca full-text com Meilisearch para descoberta rápida e precisa de conteúdo.

### Recursos Principais

- **Busca Full-Text** - Pesquisa em título, conteúdo, tags e autor
- **Autocomplete Inteligente** - Sugestões em tempo real enquanto digita
- **Filtros Avançados** - Status, tipo, tags, categorias e datas
- **Tolerância a Erros** - Typo-tolerance nativo do Meilisearch
- **Performance Ultra-Rápida** - Respostas em milissegundos
- **Highlighting** - Destaque de termos encontrados

## 🤖 Sistema de Recomendações

Engine de recomendações inteligente com Neo4j para descoberta de conteúdo e conexões relevantes.

### Recursos Principais

- **Usuários Similares** - Sugestões baseadas em seguidores em comum
- **Artigos Relacionados** - Recomendações por tags e categorias compartilhadas
- **Autores Influentes** - Descoberta de criadores de conteúdo populares
- **Tópicos de Interesse** - Identificação de áreas baseadas em interações
- **Sincronização Automática** - Observers mantêm Neo4j atualizado em tempo real
- **Graceful Degradation** - Sistema funciona mesmo se Neo4j estiver indisponível

## 🗄️ Banco de Dados

### Estrutura MongoDB

| Collection | Descrição |
|-----------|-----------|
| `users` | Usuários e autenticação |
| `articles` | Artigos e metadados |
| `article_versions` | Histórico de versões |
| `comments` | Comentários e interações |
| `likes` | Curtidas de usuários |
| `followers` | Rede social e relacionamentos |
| `personal_access_tokens` | Tokens de acesso Sanctum |

## 🔧 Qualidade de Código

### Ferramentas e Padrões

- **Pest 4.1** - Framework de testes moderno com 100% de cobertura
- **PHPStan Level 10** - Análise estática máxima sem erros
- **Laravel Pint** - Code style automático seguindo PSR-12
- **Rector** - Refactoring automático e modernização de código

### Testes

Cobertura completa incluindo testes unitários, de integração e de feature para todas as funcionalidades críticas do sistema.

## 🤝 Contribuindo

Contribuições são bem-vindas! Siga os padrões de código estabelecidos, mantenha a cobertura de testes em 100% e execute todas as verificações de qualidade antes de submeter pull requests.

## 🚀 Deploy e Produção

Sistema containerizado com Docker, pronto para deploy em qualquer ambiente que suporte containers. Configurações otimizadas para alta performance e escalabilidade horizontal.

## 📊 Performance

- **Cache Redis** - Otimização de queries e redução de carga no banco
- **Índices MongoDB** - Queries otimizadas para alta performance
- **Rate Limiting** - Proteção contra abuso e garantia de disponibilidade
- **Lazy Loading** - Relacionamentos carregados sob demanda
- **Query Optimization** - Spatie Query Builder para filtragem eficiente

## 🔒 Segurança

- **Autenticação Robusta** - Laravel Sanctum com tokens seguros
- **Hashing de Senhas** - Bcrypt para máxima segurança
- **Validação Rigorosa** - Form Requests em todos os endpoints
- **Rate Limiting** - Proteção contra força bruta e DDoS
- **Soft Deletes** - Recuperação de dados críticos

## 📄 Licença

Este projeto está sob a licença MIT.

---

**Developed with ❤️ by [Gabriel Silva](https://github.com/GabeSilvaDev)**

**Desenvolvido com ❤️ por [Gabriel Silva](https://github.com/GabeSilvaDev)**
