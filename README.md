# Knowledge Hub API

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![MongoDB](https://img.shields.io/badge/MongoDB-6.0-47A248?style=flat-square&logo=mongodb&logoColor=white)](https://mongodb.com)
[![Redis](https://img.shields.io/badge/Redis-7.0-DC382D?style=flat-square&logo=redis&logoColor=white)](https://redis.io)
[![Meilisearch](https://img.shields.io/badge/Meilisearch-1.12-FF5CAA?style=flat-square&logo=meilisearch&logoColor=white)](https://meilisearch.com)
[![Neo4j](https://img.shields.io/badge/Neo4j-5.13-008CC1?style=flat-square&logo=neo4j&logoColor=white)](https://neo4j.com)
[![Pest](https://img.shields.io/badge/Pest-4.1-8BC34A?style=flat-square&logo=pest&logoColor=white)](https://pestphp.com)
[![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?style=flat-square&logo=docker&logoColor=white)](https://docker.com)

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

## � Qualidade de Código

### Ferramentas e Padrões

- **Pest 4.1** - Framework de testes moderno com 100% de cobertura
- **PHPStan Level 10** - Análise estática máxima sem erros
- **Laravel Pint** - Code style automático seguindo PSR-12
- **Rector** - Refactoring automático e modernização de código

### Testes

Cobertura completa incluindo testes unitários, de integração e de feature para todas as funcionalidades críticas do sistema.

## 🤝 Contribuindo

Contribuições são bem-vindas! Siga os padrões de código estabelecidos, mantenha a cobertura de testes em 100% e execute todas as verificações de qualidade antes de submeter pull requests.

## � Deploy e Produção

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

## 🙏 Tecnologias

Desenvolvido com as melhores ferramentas e frameworks da atualidade: Laravel, MongoDB, Redis, Pest, PHPStan, Docker e muito mais.

---

**Desenvolvido com ❤️ por [Gabriel Silva](https://github.com/GabeSilvaDev)**