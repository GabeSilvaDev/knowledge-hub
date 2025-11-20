# 📋 Funcionalidades Pendentes - Knowledge Hub

> **Status do Projeto**: 80% completo  
> **Última atualização**: 20 de novembro de 2025

## 🎯 Visão Geral

Este documento detalha as funcionalidades que ainda precisam ser implementadas para completar 100% do SRS (Software Requirements Specification) do projeto Knowledge Hub.

---

## ❌ Funcionalidades Não Implementadas

### 1. 🔍 Sistema de Busca Avançada (RF-060 a RF-062)

**Prioridade**: 🔴 **ALTA**  
**Complexidade**: Média  
**Tempo estimado**: 16-24 horas

#### Requisitos Pendentes:

##### RF-060: Buscar Artigos
- [ ] Integrar Laravel Scout
- [ ] Configurar Meilisearch ou Elasticsearch
- [ ] Criar `SearchController`
- [ ] Criar `SearchService` e `SearchRepository`
- [ ] Implementar busca por:
  - Título do artigo
  - Conteúdo completo
  - Tags
  - Nome do autor
- [ ] Implementar paginação de resultados
- [ ] Criar testes unitários e de feature

##### RF-061: Autocomplete
- [ ] Endpoint para sugestões em tempo real
- [ ] Implementar debounce no frontend
- [ ] Cache de sugestões populares
- [ ] Limite de resultados (ex: 10 sugestões)

##### RF-062: Filtros Avançados
- [ ] Filtro por autor específico
- [ ] Filtro por tags (múltiplas)
- [ ] Filtro por intervalo de datas
- [ ] Filtro por status (published, draft, etc)
- [ ] Combinação de múltiplos filtros

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

### 2. 🌐 Sistema de Recomendações com Neo4j (RF-042)

**Prioridade**: 🟡 **MÉDIA**  
**Complexidade**: Alta  
**Tempo estimado**: 24-32 horas

#### Requisitos Pendentes:

##### RF-042: Recomendações Baseadas em Grafo
- [ ] Configurar Neo4j no Docker
- [ ] Integrar driver PHP para Neo4j
- [ ] Criar `RecommendationService`
- [ ] Criar `Neo4jRepository`
- [ ] Implementar lógica de recomendações:
  - Usuários similares (baseado em seguidores em comum)
  - Artigos relacionados (baseado em tags e categorias)
  - Autores influentes (baseado em rede de seguidores)
  - Tópicos de interesse (baseado em artigos lidos/curtidos)

#### Estrutura de Arquivos a Criar:

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
└── DTOs/
    └── RecommendationDTO.php

config/
└── neo4j.php (configuração conexão)
```

#### Endpoints a Implementar:

```php
GET  /api/recommendations/users          // Usuários recomendados
GET  /api/recommendations/articles       // Artigos recomendados
GET  /api/recommendations/authors        // Autores sugeridos
GET  /api/recommendations/topics         // Tópicos de interesse
```

#### Pacotes Necessários:

```bash
composer require laudis/neo4j-php-client
```

#### Configuração Docker (docker-compose.yml):

```yaml
services:
  neo4j:
    image: neo4j:latest
    ports:
      - "7474:7474"  # HTTP
      - "7687:7687"  # Bolt
    environment:
      NEO4J_AUTH: neo4j/password
    volumes:
      - ./storage/neo4j/data:/data
      - ./storage/neo4j/logs:/logs
```

#### Queries Neo4j a Implementar:

```cypher
// Usuários similares por seguidores em comum
MATCH (u:User {id: $userId})-[:FOLLOWS]->(common)<-[:FOLLOWS]-(similar:User)
WHERE similar.id <> $userId
RETURN similar, COUNT(common) as commonFollows
ORDER BY commonFollows DESC
LIMIT 10

// Artigos relacionados por tags
MATCH (a:Article {id: $articleId})-[:HAS_TAG]->(tag)<-[:HAS_TAG]-(related:Article)
WHERE related.id <> $articleId
RETURN related, COUNT(tag) as commonTags
ORDER BY commonTags DESC
LIMIT 10

// Autores influentes na rede
MATCH (author:User)<-[:FOLLOWS]-(follower:User)
WITH author, COUNT(follower) as followers
WHERE followers > 10
RETURN author
ORDER BY followers DESC
LIMIT 20
```

#### Sincronização de Dados:

- [ ] Criar command para sincronizar MongoDB → Neo4j
- [ ] Implementar observers para atualizar Neo4j em tempo real
- [ ] Criar job para sincronização periódica

```php
// app/Console/Commands/SyncNeo4jCommand.php
php artisan neo4j:sync
php artisan neo4j:sync --entity=users
php artisan neo4j:sync --entity=articles
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
| **Feed (RF-040 a RF-042)** | 2 | 1 | 3 | 66% |
| **Ranking (RF-050 a RF-052)** | 2 | 1 | 3 | 66% |
| **Busca (RF-060 a RF-062)** | 0 | 3 | 3 | 0% |
| **RNFs** | 5 | 0 | 5 | 100% |
| **TOTAL** | 30 | 5 | 35 | **85.7%** |

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

### Funcionalidades Avançadas ⚠️ (40%)

- ❌ Busca avançada (0%)
- ❌ Recomendações Neo4j (0%)
- ❌ Ranking de usuários (0%)

---

## 🗓️ Roadmap Sugerido

### Sprint 1 - Sistema de Busca (1-2 semanas)
**Objetivo**: Implementar busca completa com Meilisearch

1. **Semana 1**:
   - Configurar Meilisearch no Docker
   - Integrar Laravel Scout
   - Implementar RF-060 (busca básica)
   - Criar testes

2. **Semana 2**:
   - Implementar RF-061 (autocomplete)
   - Implementar RF-062 (filtros avançados)
   - Otimizar performance
   - Documentação

### Sprint 2 - Ranking de Usuários (3-5 dias)
**Objetivo**: Completar sistema de rankings

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

### Sprint 3 - Recomendações Neo4j (2-3 semanas)
**Objetivo**: Sistema de recomendações inteligente

1. **Semana 1**:
   - Configurar Neo4j
   - Modelar grafo de relacionamentos
   - Criar sincronização MongoDB → Neo4j

2. **Semana 2**:
   - Implementar queries de recomendação
   - Criar RecommendationService
   - Integrar com feed personalizado

3. **Semana 3**:
   - Otimizar performance
   - Criar testes
   - Documentação completa

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

- [ ] RF-060: Busca de artigos implementada
- [ ] RF-061: Autocomplete funcionando
- [ ] RF-062: Filtros avançados operacionais
- [ ] RF-042: Recomendações Neo4j ativas
- [ ] RF-051: Ranking de usuários implementado
- [ ] Todos os testes passando (100% coverage)
- [ ] Documentação Postman atualizada
- [ ] README atualizado com novas features
- [ ] Docker compose com todos os serviços

---

**Última revisão**: 20/11/2025  
**Versão**: 1.0  
**Autor**: Knowledge Hub Development Team
