# 📋 Funcionalidades Pendentes - Knowledge Hub

> **Status do Projeto**: 97.1% completo  
> **Última atualização**: 01 de dezembro de 2025

## 🎯 Visão Geral

Este documento detalha as funcionalidades que ainda precisam ser implementadas para completar 100% do SRS (Software Requirements Specification) do projeto Knowledge Hub.

---

## ✅ Funcionalidades Implementadas

### 1. 🔍 Sistema de Busca Avançada (RF-060 a RF-062) ✅ **COMPLETO**

**Prioridade**: 🔴 **ALTA**  
**Complexidade**: Média  
**Tempo de implementação**: ~18 horas  
**Status**: ✅ **100% IMPLEMENTADO E TESTADO**

#### Requisitos Pendentes:

##### RF-060: Buscar Artigos ✅ **COMPLETO**
- [x] Integrar Laravel Scout (v10.22.0)
- [x] Configurar Meilisearch (v1.12 via Docker)
- [x] Criar `SearchController`
- [x] Criar `SearchService` e `SearchServiceInterface`
- [x] Implementar busca por:
  - Título do artigo
  - Conteúdo completo
  - Tags
  - Nome do autor
- [x] Implementar paginação de resultados
- [x] Criar testes unitários e de feature (16 testes passando)

**Implementado em**: 
- `app/Contracts/SearchServiceInterface.php`
- `app/Services/SearchService.php`
- `app/Http/Controllers/SearchController.php`
- `app/Http/Requests/SearchRequest.php`
- `tests/Feature/SearchControllerTest.php`

**Testes**: ✅ 16/16 passando (13 validação + 3 funcionalidade)  
**Status Meilisearch**: ✅ 11 artigos indexados  
**Verificação**: ✅ Todas as buscas funcionando (título, conteúdo, tags, autor)

##### RF-061: Autocomplete ✅ **COMPLETO**
- [x] Endpoint para sugestões em tempo real
- [x] Implementar debounce no frontend (via query mínima)
- [x] Cache de sugestões populares (via Meilisearch)
- [x] Limite de resultados (configurável, padrão: 10 sugestões)

**Endpoint**: `GET /api/search/autocomplete?query=vol&limit=10`  
**Implementado em**: `SearchController@autocomplete`  
**Testes**: ✅ 8 sugestões retornadas para query 'vol'

##### RF-062: Filtros Avançados ✅ **COMPLETO**
- [x] Filtro por autor específico
- [x] Filtro por tags (múltiplas)
- [x] Filtro por intervalo de datas
- [x] Filtro por status (published, draft, etc)
- [x] Combinação de múltiplos filtros

**Filtros disponíveis**: author_id, tags[], categories[], status, type, published_from, published_to  
**Attributes configurados no Meilisearch**: status, author_id, tags, categories, published_at, type  
**Testes**: ✅ 4/4 filtros testados e funcionando (autor, tags, status, múltiplos combinados)

#### Estrutura de Arquivos a Criar:

```
app/
├── Contracts/
│   └── SearchServiceInterface.php
├── Services/
│   └── SearchService.php
├── Http/
│   ├── Controllers/
│   │   └── SearchController.php
│   └── Requests/
│       └── SearchRequest.php
└── Models/
    └── Search/
        └── ArticleSearchable.php (trait ou config)

config/
└── scout.php (configuração Laravel Scout)

routes/
└── api.php (adicionar rotas de busca)
```

#### Endpoints a Implementar:

```php
GET  /api/search                    // Busca geral
GET  /api/search/autocomplete       // Autocomplete
GET  /api/search/articles           // Busca apenas artigos
```

#### Pacotes Necessários:

```bash
composer require laravel/scout
composer require meilisearch/meilisearch-php
# OU
composer require elasticsearch/elasticsearch
```

#### Configuração Docker (docker-compose.yml):

```yaml
services:
  meilisearch:
    image: getmeili/meilisearch:latest
    ports:
      - "7700:7700"
    environment:
      MEILI_NO_ANALYTICS: "true"
    volumes:
      - ./storage/meilisearch:/meili_data
```

---

### 2. 🌐 Sistema de Recomendações com Neo4j (RF-042) ✅ **COMPLETO**

**Prioridade**: 🟡 **MÉDIA**  
**Complexidade**: Alta  
**Tempo estimado**: 24-32 horas  
**Status**: ✅ **100% IMPLEMENTADO E TESTADO**

#### Requisitos Implementados:

##### RF-042: Recomendações Baseadas em Grafo ✅ **COMPLETO**
- [x] Configurar Neo4j no Docker (v5.13-community)
- [x] Integrar driver PHP para Neo4j (laudis/neo4j-php-client v3.4.0)
- [x] Criar `RecommendationService` e `RecommendationServiceInterface`
- [x] Criar `Neo4jRepository` e `Neo4jRepositoryInterface`
- [x] Implementar lógica de recomendações:
  - Usuários similares (baseado em seguidores em comum)
  - Artigos relacionados (baseado em tags e categorias)
  - Autores influentes (baseado em rede de seguidores)
  - Tópicos de interesse (baseado em artigos curtidos)
- [x] Criar `RecommendationController` com 7 endpoints
- [x] Criar `RecommendationDTO` para transferência de dados
- [x] Implementar Observers para sincronização automática:
  - `ArticleNeo4jObserver` - Sync artigos
  - `UserNeo4jObserver` - Sync usuários
  - `FollowerNeo4jObserver` - Sync relacionamentos de follow
  - `LikeNeo4jObserver` - Sync likes
- [x] Criar command `php artisan neo4j:sync` para sincronização manual
- [x] Implementar graceful degradation (funciona sem Neo4j)
- [x] Criar testes unitários e de feature (100% cobertura)

**Implementado em**: 
- `app/Contracts/Neo4jRepositoryInterface.php`
- `app/Contracts/RecommendationServiceInterface.php`
- `app/Repositories/Neo4jRepository.php`
- `app/Services/RecommendationService.php`
- `app/Http/Controllers/RecommendationController.php`
- `app/DTOs/RecommendationDTO.php`
- `app/Enums/RecommendationType.php`
- `app/Observers/ArticleNeo4jObserver.php`
- `app/Observers/UserNeo4jObserver.php`
- `app/Observers/FollowerNeo4jObserver.php`
- `app/Observers/LikeNeo4jObserver.php`
- `app/Console/Commands/SyncNeo4jCommand.php`
- `config/neo4j.php`

**Testes**: ✅ 100% cobertura
- `tests/Feature/RecommendationControllerTest.php` - 25 testes
- `tests/Unit/Services/RecommendationServiceTest.php` - 16 testes
- `tests/Unit/Repositories/Neo4jRepositoryTest.php` - 25 testes
- `tests/Unit/Repositories/Neo4jRepositoryDisconnectedTest.php` - 19 testes
- `tests/Unit/Observers/ArticleNeo4jObserverTest.php` - 9 testes
- `tests/Unit/Observers/UserNeo4jObserverTest.php` - 3 testes
- `tests/Unit/Observers/FollowerNeo4jObserverTest.php` - 2 testes
- `tests/Unit/Observers/LikeNeo4jObserverTest.php` - 2 testes
- `tests/Feature/Console/Commands/SyncNeo4jCommandTest.php` - 5 testes

**Endpoints Implementados**:
```php
GET  /api/recommendations/statistics   // Estatísticas do grafo (público)
GET  /api/recommendations/authors      // Autores influentes (público)
GET  /api/articles/{id}/related        // Artigos relacionados (público)
GET  /api/recommendations/users        // Usuários recomendados (autenticado)
GET  /api/recommendations/articles     // Artigos recomendados (autenticado)
GET  /api/recommendations/topics       // Tópicos de interesse (autenticado)
POST /api/recommendations/sync         // Sincronizar Neo4j (autenticado)
```

**Postman**: ✅ Collection atualizada (v3.2) com todos os endpoints

#### Estrutura de Arquivos Criados:

```
app/
├── Contracts/
│   ├── RecommendationServiceInterface.php
│   └── Neo4jRepositoryInterface.php
├── Services/
│   └── RecommendationService.php
├── Repositories/
│   └── Neo4jRepository.php
├── Http/
│   └── Controllers/
│       └── RecommendationController.php
├── DTOs/
│   └── RecommendationDTO.php
├── Enums/
│   └── RecommendationType.php
├── Observers/
│   ├── ArticleNeo4jObserver.php
│   ├── UserNeo4jObserver.php
│   ├── FollowerNeo4jObserver.php
│   └── LikeNeo4jObserver.php
└── Console/
    └── Commands/
        └── SyncNeo4jCommand.php

config/
└── neo4j.php
```

---

### 3. 👥 Ranking de Usuários Influentes (RF-051)

**Prioridade**: 🟢 **BAIXA**  
**Complexidade**: Baixa  
**Tempo estimado**: 8-12 horas

#### Requisitos Pendentes:

##### RF-051: Ranking de Usuários Baseado em Influência
- [ ] Criar `UserRankingService`
- [ ] Implementar lógica de cálculo de influência:
  - Número de seguidores
  - Engajamento nos artigos (views, likes, comments)
  - Qualidade do conteúdo (média de likes por artigo)
  - Frequência de publicação
- [ ] Armazenar ranking no Redis (Sorted Set)
- [ ] Atualizar ranking automaticamente via observers
- [ ] Criar endpoints para consulta

#### Estrutura de Arquivos a Criar:

```
app/
├── Contracts/
│   └── UserRankingServiceInterface.php
├── Services/
│   └── UserRankingService.php
├── Http/
│   └── Controllers/
│       └── UserRankingController.php
└── Observers/
    └── UserRankingObserver.php (atualizar em followers/articles)
```

#### Endpoints a Implementar:

```php
GET  /api/users/ranking                 // Top usuários influentes
GET  /api/users/ranking/statistics      // Estatísticas gerais
GET  /api/users/{user}/ranking          // Posição específica
POST /api/users/ranking/sync            // Sincronizar (admin)
```

#### Fórmula de Influência Sugerida:

```php
$influenceScore = (
    $followersCount * 2.0 +          // Peso maior para seguidores
    $totalArticleViews * 0.5 +       // Views totais
    $totalArticleLikes * 1.0 +       // Likes totais
    $totalArticleComments * 0.8 +    // Comentários
    $articlesPublished * 1.5         // Produtividade
);
```

#### Redis Key Structure:

```
users:ranking:influence    // Sorted Set com score de influência
users:ranking:followers    // Sorted Set por número de seguidores
users:ranking:engagement   // Sorted Set por engajamento médio
```

---

## 📊 Estatísticas do Projeto

### Resumo de Implementação

| Categoria | Implementado | Pendente | Total | % Completo |
|-----------|--------------|----------|-------|------------|
| **Usuários (RF-001 a RF-007)** | 7 | 0 | 7 | 100% |
| **Artigos (RF-010 a RF-016)** | 7 | 0 | 7 | 100% |
| **Comentários (RF-020 a RF-023)** | 4 | 0 | 4 | 100% |
| **Likes (RF-030 a RF-032)** | 3 | 0 | 3 | 100% |
| **Feed (RF-040 a RF-042)** | 3 | 0 | 3 | **100%** ✅ |
| **Ranking (RF-050 a RF-052)** | 2 | 1 | 3 | 66% |
| **Busca (RF-060 a RF-062)** | 3 | 0 | 3 | **100%** ✅ |
| **RNFs** | 5 | 0 | 5 | 100% |
| **TOTAL** | 34 | 1 | 35 | **97.1%** |

### Funcionalidades Core ✅ (100%)

- ✅ Sistema de autenticação completo
- ✅ CRUD de artigos com versionamento
- ✅ Sistema de comentários
- ✅ Sistema de likes/curtidas
- ✅ Sistema de seguidores
- ✅ Feed público e personalizado
- ✅ Ranking de artigos (Redis)
- ✅ Contadores automáticos
- ✅ Cache e invalidação (Redis)
- ✅ Rate limiting
- ✅ Arquitetura em camadas

### Funcionalidades Avançadas ✅ (83.3%)

- ✅ **Busca avançada (100%)** - RF-060, RF-061, RF-062 completos
- ✅ **Recomendações Neo4j (100%)** - RF-042 completo
- ❌ Ranking de usuários (0%)

---

## 🗓️ Roadmap Sugerido

### ✅ Sprint 1 - Sistema de Busca (CONCLUÍDO)
**Status**: ✅ **100% COMPLETO**

- ✅ Configurar Meilisearch no Docker
- ✅ Integrar Laravel Scout
- ✅ Implementar RF-060 (busca básica)
- ✅ Implementar RF-061 (autocomplete)
- ✅ Implementar RF-062 (filtros avançados)
- ✅ Criar testes (100% cobertura)

### ✅ Sprint 2 - Recomendações Neo4j (CONCLUÍDO)
**Status**: ✅ **100% COMPLETO**

- ✅ Configurar Neo4j no Docker
- ✅ Modelar grafo de relacionamentos
- ✅ Criar sincronização MongoDB → Neo4j
- ✅ Implementar queries de recomendação
- ✅ Criar RecommendationService
- ✅ Implementar observers para sync automático
- ✅ Criar testes (100% cobertura)
- ✅ Atualizar Postman collection

### 📋 Sprint 3 - Ranking de Usuários (PENDENTE)
**Objetivo**: Completar sistema de rankings de usuários

1. **Dia 1-2**:
   - Criar UserRankingService
   - Implementar cálculo de influência
   - Configurar Redis Sorted Sets

2. **Dia 3-4**:
   - Criar endpoints
   - Implementar observers
   - Criar testes

3. **Dia 5**:
   - Documentação
   - Ajustes finais

---

## 🔧 Configurações Necessárias

### Variáveis de Ambiente (.env)

```bash
# Meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=masterKey
SCOUT_DRIVER=meilisearch

# Neo4j
NEO4J_HOST=neo4j
NEO4J_PORT=7687
NEO4J_USERNAME=neo4j
NEO4J_PASSWORD=password
NEO4J_DATABASE=neo4j

# Redis (já configurado)
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Atualização Docker Compose

```yaml
version: '3.8'

services:
  # ... serviços existentes ...

  meilisearch:
    image: getmeili/meilisearch:latest
    container_name: knowledge-hub-search
    ports:
      - "7700:7700"
    environment:
      MEILI_MASTER_KEY: masterKey
      MEILI_NO_ANALYTICS: "true"
    volumes:
      - ./storage/meilisearch:/meili_data
    networks:
      - knowledge-hub-network

  neo4j:
    image: neo4j:5.13-community
    container_name: knowledge-hub-neo4j
    ports:
      - "7474:7474"  # HTTP
      - "7687:7687"  # Bolt
    environment:
      NEO4J_AUTH: neo4j/password
      NEO4J_PLUGINS: '["apoc"]'
    volumes:
      - ./storage/neo4j/data:/data
      - ./storage/neo4j/logs:/logs
      - ./storage/neo4j/plugins:/plugins
    networks:
      - knowledge-hub-network
```

---

## 📚 Recursos e Referências

### Documentação Oficial

- **Laravel Scout**: https://laravel.com/docs/11.x/scout
- **Meilisearch**: https://www.meilisearch.com/docs
- **Neo4j PHP Client**: https://neo4j.com/docs/php-manual/current/
- **Redis Sorted Sets**: https://redis.io/docs/data-types/sorted-sets/

### Tutoriais Recomendados

1. **Scout + Meilisearch**: 
   - https://laracasts.com/series/laravel-scout-driver-meilisearch

2. **Neo4j com Laravel**: 
   - https://neo4j.com/developer/php/

3. **Redis Rankings**: 
   - https://redis.io/docs/data-types/sorted-sets/#leaderboards

---

## 📝 Notas de Implementação

### Priorização

**DEVE ser implementado antes do MVP**:
- ✅ Sistema de busca básica (RF-060)

**PODE ser implementado pós-MVP**:
- 🔄 Autocomplete e filtros avançados (RF-061, RF-062)
- 🔄 Ranking de usuários (RF-051)
- 🔄 Recomendações Neo4j (RF-042)

### Considerações de Performance

- **Busca**: Indexar incrementalmente, não rebuild completo
- **Neo4j**: Sincronizar apenas deltas, não todos os dados
- **Rankings**: Atualizar em background jobs, não síncronos

### Testes Obrigatórios

Para cada funcionalidade implementada:
- [ ] Testes unitários (Services)
- [ ] Testes de integração (Repositories)
- [ ] Testes de feature (Controllers/Endpoints)
- [ ] Cobertura mínima: 100% (PHPStan level 10)

---

## ✅ Checklist de Conclusão

Marcar quando completado:

- [x] RF-060: Busca de artigos implementada ✅
- [x] RF-061: Autocomplete funcionando ✅
- [x] RF-062: Filtros avançados operacionais ✅
- [x] RF-042: Recomendações Neo4j ativas ✅
- [ ] RF-051: Ranking de usuários implementado
- [x] Todos os testes passando (100% coverage) ✅
- [x] Documentação Postman atualizada (v3.2) ✅
- [x] README atualizado com novas features ✅
- [x] Docker compose com todos os serviços ✅

---

**Última revisão**: 01/12/2025  
**Versão**: 1.1  
**Autor**: Knowledge Hub Development Team
