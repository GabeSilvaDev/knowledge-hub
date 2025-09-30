# Knowledge Hub

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![MongoDB](https://img.shields.io/badge/MongoDB-6.0-47A248?style=flat-square&logo=mongodb&logoColor=white)](https://mongodb.com)
[![Pest](https://img.shields.io/badge/Pest-4.1-8BC34A?style=flat-square&logo=pest&logoColor=white)](https://pestphp.com)
[![Docker](https://img.shields.io/badge/Docker-Latest-2496ED?style=flat-square&logo=docker&logoColor=white)](https://docker.com)

Um sistema moderno de gerenciamento de conhecimento construído com Laravel 12 e MongoDB, projetado para armazenar, organizar e compartilhar conhecimento de forma eficiente.

## ✨ Características

- 🚀 **Laravel 12** - Framework PHP mais recente
- 🍃 **MongoDB** - Banco de dados NoSQL flexível e escalável
- 🧪 **Pest 4** - Framework de testes moderno e expressivo
- 🐳 **Docker** - Ambiente de desenvolvimento containerizado
- 🔐 **Autenticação** - Sistema de usuários integrado
- 📝 **Documentação** - Estrutura flexível para diferentes tipos de conteúdo
- ⚡ **Performance** - Otimizado para alta performance

## 🛠️ Tecnologias

| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| PHP | 8.4 | Linguagem de programação |
| Laravel | 12.0 | Framework web PHP |
| MongoDB | 6.0 | Banco de dados NoSQL |
| MongoDB Laravel | 5.5 | Driver oficial Laravel para MongoDB |
| Pest | 4.1 | Framework de testes |
| Docker | Latest | Containerização |
| Docker Compose | Latest | Orquestração de containers |

## 🏗️ Arquitetura

```
knowledge-hub/
├── app/
│   ├── Http/Controllers/     # Controladores da aplicação
│   ├── Models/              # Modelos Eloquent para MongoDB
│   └── Providers/           # Service Providers
├── config/
│   ├── database.php         # Configuração do MongoDB
│   └── app.php             # Configurações da aplicação
├── database/
│   ├── factories/          # Factories para geração de dados
│   └── seeders/           # Seeders para população inicial
├── tests/
│   ├── Feature/           # Testes de integração
│   ├── Unit/             # Testes unitários
│   └── Pest.php          # Configuração do Pest
├── docker-compose.yml    # Configuração dos containers
├── Dockerfile           # Imagem customizada do Laravel
└── README.md           # Este arquivo
```

## 🚀 Início Rápido

### Pré-requisitos

- [Docker](https://docker.com) e [Docker Compose](https://docs.docker.com/compose/)
- [Git](https://git-scm.com)

### Instalação

1. **Clone o repositório**
   ```bash
   git clone <repository-url>
   cd knowledge-hub
   ```

2. **Configure o ambiente**
   ```bash
   cp .env.example .env
   ```

3. **Inicie os containers**
   ```bash
   docker-compose up -d
   ```

4. **Instale as dependências**
   ```bash
   docker exec -it knowledge-hub-knowledge-hub-1 composer install
   ```

5. **Gere a chave da aplicação**
   ```bash
   docker exec -it knowledge-hub-knowledge-hub-1 php artisan key:generate
   ```

6. **Execute os seeders (opcional)**
   ```bash
   docker exec -it knowledge-hub-knowledge-hub-1 php artisan db:seed
   ```

### 🎯 Acesso

- **Aplicação**: http://localhost:8004
- **MongoDB**: localhost:27017

## 🧪 Testes

Este projeto utiliza o **Pest 4** como framework de testes, proporcionando uma sintaxe moderna e expressiva.

### Executar todos os testes
```bash
docker exec -it knowledge-hub-knowledge-hub-1 ./vendor/bin/pest
```

### Executar testes com cobertura
```bash
docker exec -it knowledge-hub-knowledge-hub-1 ./vendor/bin/pest --coverage
```

### Executar testes específicos
```bash
# Testes unitários
docker exec -it knowledge-hub-knowledge-hub-1 ./vendor/bin/pest tests/Unit

# Testes feature
docker exec -it knowledge-hub-knowledge-hub-1 ./vendor/bin/pest tests/Feature

# Arquivo específico
docker exec -it knowledge-hub-knowledge-hub-1 ./vendor/bin/pest tests/Feature/UserModelTest.php
```

### Estrutura de Testes

- **Unit Tests**: Testam componentes isolados (models, helpers)
- **Feature Tests**: Testam fluxos completos da aplicação
- **Database Testing**: Testes automaticamente limpam o banco MongoDB entre execuções

## 🗄️ Banco de Dados

### MongoDB

O projeto utiliza MongoDB como banco de dados principal, oferecendo:

- **Schema flexível**: Documentos podem ter estruturas diferentes
- **Escalabilidade horizontal**: Fácil distribuição em múltiplos servidores
- **Performance**: Otimizado para leitura/escrita de grandes volumes

### Visualização dos Dados

#### Via MongoDB Shell
```bash
# Acessar o shell
docker exec -it knowledge-hub-mongo-1 mongosh

# Comandos úteis
show dbs                    # Listar bancos
use knowledge_hub          # Selecionar banco
show collections          # Listar collections
db.users.find().pretty()  # Visualizar usuários
```

#### Via Laravel Tinker
```bash
docker exec -it knowledge-hub-knowledge-hub-1 php artisan tinker
```
```php
// Exemplos de uso
User::all()                         # Todos os usuários
User::factory(5)->create()          # Criar 5 usuários
User::where('name', 'João')->get()  # Buscar por nome
```

### Configuração

A configuração do MongoDB está em:
- `config/database.php` - Configuração da conexão
- `.env` - Variáveis de ambiente
- `.env.testing` - Configuração para testes

## 📁 Modelos

### User Model

O modelo User está configurado para trabalhar com MongoDB:

```php
<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $connection = 'mongodb';
    protected $collection = 'users';
    
    protected $fillable = [
        'name', 'email', 'password',
    ];
    
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

## 🛠️ Desenvolvimento

### Comandos Úteis

```bash
# Acessar container da aplicação
docker exec -it knowledge-hub-knowledge-hub-1 bash

# Acessar MongoDB shell
docker exec -it knowledge-hub-mongo-1 mongosh

# Ver logs da aplicação
docker logs knowledge-hub-knowledge-hub-1 -f

# Ver logs do MongoDB
docker logs knowledge-hub-mongo-1 -f

# Rebuild dos containers
docker-compose down && docker-compose up -d --build
```

### Estrutura do Docker

```yaml
services:
  knowledge-hub:
    build: .
    ports:
      - "8004:8004"
    depends_on:
      - mongo
    
  mongo:
    image: mongo:6.0
    ports:
      - "27017:27017"
    volumes:
      - mongo_data:/data/db
```

## 🔒 Configuração de Ambiente

### Variáveis Principais

```env
# Aplicação
APP_NAME=Knowledge Hub
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8004

# MongoDB
DB_CONNECTION=mongodb
DB_HOST=mongo
DB_PORT=27017
DB_DATABASE=knowledge_hub
DB_USERNAME=
DB_PASSWORD=
```

### Ambiente de Teste

O projeto possui configuração separada para testes em `.env.testing`:

```env
DB_CONNECTION=mongodb
DB_DATABASE=knowledge_hub_test
SESSION_DRIVER=array
CACHE_STORE=array
```

## 📈 Performance

### Otimizações Implementadas

- **Autoloader otimizado** com Composer
- **Cache de configuração** para produção
- **Índices MongoDB** para consultas frequentes
- **Lazy loading** de relacionamentos

### Monitoramento

```bash
# Verificar performance dos testes
docker exec -it knowledge-hub-knowledge-hub-1 ./vendor/bin/pest --profile

# Estatísticas do MongoDB
docker exec -it knowledge-hub-mongo-1 mongosh --eval "db.stats()"
```

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

### Padrões de Código

- Siga as [PSR-12](https://www.php-fig.org/psr/psr-12/) para PHP
- Use **Pest** para novos testes
- Documente funções complexas
- Mantenha os testes atualizados

## 📝 Changelog

### [1.0.0] - 2025-09-30

#### Adicionado
- Configuração inicial do Laravel 12
- Integração com MongoDB
- Sistema de autenticação
- Framework de testes Pest 4
- Ambiente Docker
- Documentação completa

## 📄 Licença

Este projeto está licenciado sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 🆘 Suporte

Se você encontrar algum problema ou tiver dúvidas:

1. Verifique a [documentação](#-início-rápido)
2. Consulte os [logs](#comandos-úteis)
3. Execute os [testes](#-testes)
4. Abra uma [issue](link-para-issues)

## 🙏 Agradecimentos

- [Laravel](https://laravel.com) - Framework PHP incrível
- [MongoDB](https://mongodb.com) - Banco de dados NoSQL flexível
- [Pest](https://pestphp.com) - Framework de testes moderno
- [Docker](https://docker.com) - Containerização simplificada

---
