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
- 🔐 **Autenticação Sanctum** - Sistema de autenticação via API tokens
- 📝 **Documentação** - Estrutura flexível para diferentes tipos de conteúdo
- ⚡ **Performance** - Otimizado para alta performance
- 🔑 **API RESTful** - Endpoints seguros e bem documentados

## 🛠️ Tecnologias

| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| PHP | 8.4 | Linguagem de programação |
| Laravel | 12.0 | Framework web PHP |
| Laravel Sanctum | 4.2 | Autenticação via API tokens |
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
│   │   └── AuthController.php  # Autenticação Sanctum
│   ├── Models/              # Modelos Eloquent para MongoDB
│   └── Providers/           # Service Providers
├── config/
│   ├── database.php         # Configuração do MongoDB
│   └── app.php             # Configurações da aplicação
├── database/
│   ├── factories/          # Factories para geração de dados
│   ├── migrations/         # Migrations para MongoDB
│   └── seeders/           # Seeders para população inicial
├── routes/
│   ├── web.php            # Rotas web
│   └── api.php            # Rotas da API com Sanctum
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

6. **Execute as migrations**
   ```bash
   docker exec -it knowledge-hub-knowledge-hub-1 php artisan migrate
   ```

7. **Execute os seeders (opcional)**
   ```bash
   docker exec -it knowledge-hub-knowledge-hub-1 php artisan db:seed
   ```

### 🎯 Acesso

- **Aplicação**: http://localhost:8004
- **API**: http://localhost:8004/api
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

## � Autenticação com Laravel Sanctum

O Knowledge Hub utiliza **Laravel Sanctum** para autenticação via API tokens, proporcionando uma solução simples e segura para SPAs, aplicativos móveis e APIs simples baseadas em tokens.

### Endpoints Disponíveis

#### Registro de Usuário
```bash
POST /api/register
Content-Type: application/json

{
  "name": "João Silva",
  "email": "joao@example.com",
  "username": "joaosilva",
  "password": "senha_segura_123",
  "password_confirmation": "senha_segura_123",
  "bio": "Desenvolvedor Full Stack",
  "avatar_url": "https://example.com/avatar.jpg"
}
```

**Resposta de Sucesso (201):**
```json
{
  "message": "User registered successfully",
  "user": {
    "_id": "507f1f77bcf86cd799439011",
    "name": "João Silva",
    "email": "joao@example.com",
    "username": "joaosilva",
    "bio": "Desenvolvedor Full Stack",
    "avatar_url": "https://example.com/avatar.jpg",
    "roles": ["user"],
    "created_at": "2025-10-04T18:30:00.000000Z",
    "updated_at": "2025-10-04T18:30:00.000000Z"
  },
  "access_token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "token_type": "Bearer"
}
```

#### Login
```bash
POST /api/login
Content-Type: application/json

{
  "email": "joao@example.com",
  "password": "senha_segura_123"
}
```

**Resposta de Sucesso (200):**
```json
{
  "message": "Login successful",
  "user": {
    "_id": "507f1f77bcf86cd799439011",
    "name": "João Silva",
    "email": "joao@example.com",
    "username": "joaosilva",
    "last_login_at": "2025-10-04T18:35:00.000000Z"
  },
  "access_token": "2|xxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "token_type": "Bearer"
}
```

#### Obter Usuário Autenticado
```bash
GET /api/me
Authorization: Bearer {seu_token_aqui}
```

**Resposta de Sucesso (200):**
```json
{
  "user": {
    "_id": "507f1f77bcf86cd799439011",
    "name": "João Silva",
    "email": "joao@example.com",
    "username": "joaosilva",
    "roles": ["user"]
  }
}
```

#### Logout
```bash
POST /api/logout
Authorization: Bearer {seu_token_aqui}
```

**Resposta de Sucesso (200):**
```json
{
  "message": "Logged out successfully"
}
```

#### Revogar Todos os Tokens
```bash
POST /api/tokens/revoke-all
Authorization: Bearer {seu_token_aqui}
```

**Resposta de Sucesso (200):**
```json
{
  "message": "All tokens revoked successfully"
}
```

### Exemplos de Uso

#### Com cURL
```bash
# Registro
curl -X POST http://localhost:8004/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@example.com",
    "username": "joaosilva",
    "password": "senha123",
    "password_confirmation": "senha123"
  }'

# Login
curl -X POST http://localhost:8004/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@example.com",
    "password": "senha123"
  }'

# Acessar rota protegida
curl -X GET http://localhost:8004/api/me \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"

# Logout
curl -X POST http://localhost:8004/api/logout \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

#### Com JavaScript (Fetch API)
```javascript
// Registro
const register = async () => {
  const response = await fetch('http://localhost:8004/api/register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      name: 'João Silva',
      email: 'joao@example.com',
      username: 'joaosilva',
      password: 'senha123',
      password_confirmation: 'senha123'
    })
  });
  
  const data = await response.json();
  localStorage.setItem('token', data.access_token);
  return data;
};

// Login
const login = async (email, password) => {
  const response = await fetch('http://localhost:8004/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  localStorage.setItem('token', data.access_token);
  return data;
};

// Fazer requisição autenticada
const getAuthenticatedUser = async () => {
  const token = localStorage.getItem('token');
  
  const response = await fetch('http://localhost:8004/api/me', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  return await response.json();
};

// Logout
const logout = async () => {
  const token = localStorage.getItem('token');
  
  await fetch('http://localhost:8004/api/logout', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  localStorage.removeItem('token');
};
```

#### Com Axios
```javascript
import axios from 'axios';

// Configurar instância do Axios
const api = axios.create({
  baseURL: 'http://localhost:8004/api',
  headers: {
    'Content-Type': 'application/json'
  }
});

// Interceptor para adicionar token automaticamente
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Usar a API
const register = async (userData) => {
  const { data } = await api.post('/register', userData);
  localStorage.setItem('token', data.access_token);
  return data;
};

const login = async (credentials) => {
  const { data } = await api.post('/login', credentials);
  localStorage.setItem('token', data.access_token);
  return data;
};

const getMe = async () => {
  const { data } = await api.get('/me');
  return data;
};

const logout = async () => {
  await api.post('/logout');
  localStorage.removeItem('token');
};
```

### Configuração do Sanctum

O Sanctum está configurado em `config/sanctum.php`:

```php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1'
    )),
    
    'guard' => ['web'],
    
    'expiration' => null, // Tokens não expiram
    
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
];
```

### Segurança

- ✅ Senhas são automaticamente hasheadas com bcrypt
- ✅ Tokens são gerados de forma segura e única
- ✅ Email e username devem ser únicos no sistema
- ✅ Validação de senha confirmada no registro
- ✅ Timestamps de último login são atualizados automaticamente

### Personalizações

#### Adicionar campos customizados ao registro
Edite `app/Http/Controllers/AuthController.php`:

```php
public function register(Request $request): JsonResponse
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'unique:users'],
        // Adicione mais campos aqui
        'company' => ['nullable', 'string', 'max:255'],
    ]);
    
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'company' => $validated['company'] ?? null,
        // ...
    ]);
}
```

#### Definir expiração de tokens
Em `config/sanctum.php`:

```php
'expiration' => 60 * 24, // 24 horas
```

#### Adicionar abilities (permissões) aos tokens
```php
$token = $user->createToken('auth_token', ['read', 'write'])->plainTextToken;
```

## �🗄️ Banco de Dados

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

O modelo User está configurado para trabalhar com MongoDB e Sanctum:

```php
<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens; // Trait do Sanctum para tokens
    
    protected $connection = 'mongodb';
    protected $collection = 'users';
    
    protected $fillable = [
        'name', 
        'email', 
        'username',
        'password',
        'bio',
        'avatar_url',
        'roles',
    ];
    
    protected $hidden = [
        'password', 
        'remember_token',
    ];
    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'roles' => 'array',
            'last_login_at' => 'datetime',
        ];
    }
}
```

### Collections MongoDB

#### users
Armazena informações dos usuários:
- `_id`: ID único do MongoDB
- `name`: Nome completo
- `email`: Email único
- `username`: Nome de usuário único
- `password`: Senha hasheada
- `bio`: Biografia (opcional)
- `avatar_url`: URL do avatar (opcional)
- `roles`: Array de roles/permissões
- `email_verified_at`: Data de verificação do email
- `last_login_at`: Data do último login
- `remember_token`: Token de "lembrar-me"
- `created_at`: Data de criação
- `updated_at`: Data de atualização

#### personal_access_tokens
Armazena os tokens do Sanctum:
- `_id`: ID único do MongoDB
- `tokenable_type`: Tipo da entidade (User)
- `tokenable_id`: ID do usuário
- `name`: Nome do token
- `token`: Hash do token
- `abilities`: Permissões do token (JSON)
- `last_used_at`: Última utilização
- `expires_at`: Data de expiração
- `created_at`: Data de criação
- `updated_at`: Data de atualização

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

### [1.0.0] - 2025-10-04

#### Adicionado
- Configuração inicial do Laravel 12
- Integração com MongoDB
- **Sistema de autenticação com Laravel Sanctum**
- **Endpoints de API RESTful**
- Framework de testes Pest 4
- Ambiente Docker
- Documentação completa
- Migrations para users e personal_access_tokens
- AuthController com registro, login, logout e perfil
- Rotas de API protegidas

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
