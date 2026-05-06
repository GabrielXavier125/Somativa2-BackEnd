# Documentação Técnica — PokéDex Somativa
**Projeto:** Aplicação de consulta e gerenciamento de Pokémons  
**Framework:** Laravel 13  
**PHP:** 8.3+  
**Banco de Dados:** MySQL (PokemonTB)  
**Ambiente de Desenvolvimento:** Laragon  

---

## Sumário

1. [Ponto de Partida — Base Laravel](#1-ponto-de-partida--base-laravel)
2. [Estrutura Final do Projeto](#2-estrutura-final-do-projeto)
3. [Banco de Dados](#3-banco-de-dados)
4. [Rotas](#4-rotas)
5. [Controllers](#5-controllers)
6. [Models](#6-models)
7. [Views](#7-views)
8. [Seeder](#8-seeder)
9. [Storage de Imagens](#9-storage-de-imagens)
10. [Configuração do Ambiente (.env)](#10-configuração-do-ambiente-env)
11. [Fluxo Completo da Aplicação](#11-fluxo-completo-da-aplicação)
12. [Comunicação entre os Componentes](#12-comunicação-entre-os-componentes)
13. [O que Existia × O que Foi Criado](#13-o-que-existia--o-que-foi-criado)

---

## 1. Ponto de Partida — Base Laravel

O projeto foi iniciado a partir do **esqueleto padrão do Laravel 13**, gerado pelo comando:

```bash
composer create-project laravel/laravel Pokemon_Somativa
```

### O que a base Laravel já fornecia

| Componente | Descrição |
|---|---|
| `app/Http/Controllers/Controller.php` | Classe base abstrata para todos os controllers |
| `app/Models/User.php` | Model de usuário com autenticação |
| `app/Providers/AppServiceProvider.php` | Service provider vazio para customizações |
| `routes/web.php` | Arquivo de rotas web com uma única rota `/` |
| `routes/api.php` | Arquivo de rotas de API |
| `routes/console.php` | Arquivo de comandos Artisan |
| `resources/views/welcome.blade.php` | Página de boas-vindas padrão do Laravel |
| `database/migrations/` | 3 migrations padrão (users, cache, jobs) |
| `resources/css/app.css` | CSS configurado com Tailwind v4 via Vite |
| `resources/js/app.js` | Arquivo JS vazio |
| `vite.config.js` | Configuração do Vite com plugin Laravel + Tailwind |
| `composer.json` | Dependências PHP do projeto |
| `package.json` | Dependências frontend (Tailwind, Vite) |
| `.env` | Configurações de ambiente (SQLite por padrão) |

### O que já existia em routes/api.php (pré-projeto)

O arquivo `routes/api.php` já possuía duas rotas de exemplo conectadas à API pública `dummyjson.com`, usadas como exercício prático anterior:

```
GET  /api/user/{id}   → Busca usuário por ID (dummyjson.com)
POST /api/user/novo   → Cria novo usuário (dummyjson.com)
```

Essas rotas foram **mantidas sem alteração**, pois fazem parte do histórico do projeto.

---

## 2. Estrutura Final do Projeto

Abaixo, a estrutura completa com indicação do que foi **criado**, **modificado** ou **mantido**:

```
Pokemon_Somativa/
│
├── app/
│   └── Http/
│   │   └── Controllers/
│   │       ├── Controller.php            [MANTIDO - base Laravel]
│   │       ├── PokemonController.php     [CRIADO  - consulta PokeAPI + banco local]
│   │       └── MeuPokemonController.php  [CRIADO  - CRUD de Pokémons próprios]
│   └── Models/
│       ├── User.php                      [MANTIDO - base Laravel]
│       └── MeuPokemon.php                [CRIADO  - model da tabela meus_pokemons]
│
├── database/
│   ├── migrations/
│   │   ├── ..._create_users_table.php            [MANTIDO]
│   │   ├── ..._create_cache_table.php            [MANTIDO]
│   │   ├── ..._create_jobs_table.php             [MANTIDO]
│   │   └── 2026_05_05_..._create_meus_pokemons_table.php  [CRIADO]
│   └── seeders/
│       ├── DatabaseSeeder.php            [MANTIDO]
│       └── MeusPokemonsSeeder.php        [CRIADO  - popula os 3 Pokémons iniciais]
│
├── Meus_Pokemons/                        [PASTA EXTERNA - imagens originais]
│   ├── Aethelfin.png
│   ├── Foliagrove.png
│   └── Galehawk.png
│
├── resources/
│   ├── css/app.css                       [MANTIDO]
│   ├── js/app.js                         [MANTIDO]
│   └── views/
│       ├── welcome.blade.php             [MODIFICADO - substituído completamente]
│       ├── interface/
│       │   └── show.blade.php            [MANTIDO - arquivo vazio original]
│       └── pokemon/
│           └── create.blade.php          [CRIADO  - formulário de criação]
│
├── routes/
│   ├── web.php                           [MODIFICADO - novas rotas adicionadas]
│   ├── api.php                           [MANTIDO - rotas de exemplo dummyjson]
│   └── console.php                       [MANTIDO]
│
├── storage/
│   └── app/
│       └── public/
│           └── pokemon-images/           [CRIADO  - imagens servidas publicamente]
│               ├── Aethelfin.png
│               ├── Foliagrove.png
│               └── Galehawk.png
│
├── public/
│   └── storage -> storage/app/public     [CRIADO  - symlink via artisan storage:link]
│
└── .env                                  [MODIFICADO - trocado SQLite por MySQL]
```

---

## 3. Banco de Dados

### Alteração de SQLite para MySQL

A base Laravel vem configurada com **SQLite** por padrão. O projeto foi migrado para **MySQL** rodando no Laragon, com as seguintes alterações no `.env`:

| Variável | Antes (base Laravel) | Depois (projeto) |
|---|---|---|
| `DB_CONNECTION` | `sqlite` | `mysql` |
| `DB_HOST` | *(comentado)* | `127.0.0.1` |
| `DB_PORT` | *(comentado)* | `3306` |
| `DB_DATABASE` | *(comentado)* | `PokemonTB` |
| `DB_USERNAME` | *(comentado)* | `root` |
| `DB_PASSWORD` | *(comentado)* | `senaisp` |
| `SESSION_DRIVER` | `database` | `file` |
| `CACHE_STORE` | `database` | `file` |
| `QUEUE_CONNECTION` | `database` | `sync` |

> `SESSION_DRIVER` e `CACHE_STORE` foram alterados para `file` para evitar dependência das tabelas de sessão/cache no banco (que existiam apenas para SQLite).

### Tabela criada: `meus_pokemons`

```
Migration: 2026_05_05_173917_create_meus_pokemons_table.php
```

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | BIGINT UNSIGNED AI | Chave primária |
| `nome` | VARCHAR(255) | Nome do Pokémon |
| `imagem` | VARCHAR(255) nullable | Caminho relativo no storage |
| `tipos` | VARCHAR(255) | Tipos separados por vírgula (ex: `water,rock`) |
| `altura` | DECIMAL(5,2) | Altura em metros |
| `peso` | DECIMAL(6,2) | Peso em quilogramas |
| `hp` | SMALLINT UNSIGNED | Estatística HP (1–255) |
| `ataque` | SMALLINT UNSIGNED | Estatística Ataque (1–255) |
| `defesa` | SMALLINT UNSIGNED | Estatística Defesa (1–255) |
| `ataque_especial` | SMALLINT UNSIGNED | Estatística Ataque Especial (1–255) |
| `defesa_especial` | SMALLINT UNSIGNED | Estatística Defesa Especial (1–255) |
| `velocidade` | SMALLINT UNSIGNED | Estatística Velocidade (1–255) |
| `habilidades` | VARCHAR(255) nullable | Habilidades separadas por vírgula |
| `created_at` | TIMESTAMP | Criado em |
| `updated_at` | TIMESTAMP | Atualizado em |

---

## 4. Rotas

### routes/web.php — Arquivo modificado

```php
// Pokédex (API pública)
GET  /                      → PokemonController@index     (home)
POST /pokemon/buscar        → PokemonController@search    (pokemon.search)
GET  /pokemon/aleatorio     → PokemonController@random    (pokemon.random)

// Meus Pokémons (banco de dados)
GET    /meu-pokemon/criar        → MeuPokemonController@create   (meu-pokemon.create)
POST   /meu-pokemon              → MeuPokemonController@store    (meu-pokemon.store)
DELETE /meu-pokemon/{meuPokemon} → MeuPokemonController@destroy  (meu-pokemon.destroy)
```

### routes/api.php — Mantido da base original

```php
GET  /api/user/{id}   → Busca usuário na dummyjson.com
POST /api/user/novo   → Cria usuário na dummyjson.com
```

### Diferença entre web.php e api.php

| Arquivo | Prefixo | Middleware | Uso |
|---|---|---|---|
| `web.php` | *(nenhum)* | `web` (sessões, CSRF) | Interface do usuário (HTML) |
| `api.php` | `/api` | `api` (stateless) | Endpoints JSON sem estado |

---

## 5. Controllers

### PokemonController — CRIADO

**Arquivo:** `app/Http/Controllers/PokemonController.php`  
**Responsabilidade:** Gerenciar consultas de Pokémons (API externa e banco local)

#### Métodos

| Método | Rota | Descrição |
|---|---|---|
| `index()` | `GET /` | Carrega todos os Pokémons do banco e exibe a home |
| `search(Request)` | `POST /pokemon/buscar` | Busca com prioridade banco local → PokeAPI |
| `random()` | `GET /pokemon/aleatorio` | Busca aleatória na PokeAPI (IDs 1–1025) |
| `formatLocal(MeuPokemon)` | *(privado)* | Converte model do banco para array padrão da view |
| `format(array)` | *(privado)* | Converte resposta JSON da PokeAPI para array padrão |

#### Lógica de busca (search)

```
1. Normaliza o nome para minúsculo
2. Consulta a tabela meus_pokemons com LOWER(nome) = ?
   ├── ENCONTROU → formata via formatLocal() e exibe
   └── NÃO ENCONTROU → consulta PokeAPI
       ├── SUCESSO → formata via format() e exibe
       └── FALHA   → retorna mensagem de erro
```

#### Por que dois métodos de formato?

A PokeAPI retorna JSON com estrutura própria (heights em decímetros, weights em hectogramas, sprites aninhados). O banco local armazena metros e kg. Os métodos `format()` e `formatLocal()` normalizam esses dados em um **único formato de array** que a view `welcome.blade.php` consome, sem precisar saber de onde veio o dado.

---

### MeuPokemonController — CRIADO

**Arquivo:** `app/Http/Controllers/MeuPokemonController.php`  
**Responsabilidade:** CRUD de Pokémons próprios cadastrados no banco

#### Métodos

| Método | Rota | Descrição |
|---|---|---|
| `create()` | `GET /meu-pokemon/criar` | Exibe formulário de criação |
| `store(Request)` | `POST /meu-pokemon` | Valida, salva imagem e persiste no banco |
| `destroy(MeuPokemon)` | `DELETE /meu-pokemon/{id}` | Remove registro e apaga imagem do storage |

#### Validações no store()

```
nome            → obrigatório, string, máx. 100 caracteres
imagem          → opcional, deve ser imagem, máx. 2MB
tipo1           → obrigatório, string
tipo2           → opcional, string
altura          → obrigatório, numérico, mínimo 0
peso            → obrigatório, numérico, mínimo 0
hp/ataque/...   → obrigatório, inteiro, entre 1 e 255
habilidades     → opcional, string, máx. 255 caracteres
```

#### Upload de imagem

```
1. Verifica se há arquivo no request
2. Armazena em storage/app/public/pokemon-images/ (disco 'public')
3. Salva o caminho relativo (pokemon-images/nome.ext) no banco
4. Storage::url() gera a URL pública /storage/pokemon-images/nome.ext
```

---

## 6. Models

### MeuPokemon — CRIADO

**Arquivo:** `app/Models/MeuPokemon.php`  
**Tabela:** `meus_pokemons`

```php
protected $table    = 'meus_pokemons';
protected $fillable = ['nome', 'imagem', 'tipos', 'altura', 'peso',
                       'hp', 'ataque', 'defesa', 'ataque_especial',
                       'defesa_especial', 'velocidade', 'habilidades'];
```

#### Métodos auxiliares

| Método | Retorno | Descrição |
|---|---|---|
| `tiposArray()` | `array` | Divide a string `"water,rock"` em `["water","rock"]` |
| `stats()` | `array` | Retorna as 6 stats formatadas com label em português |

Esses métodos são usados diretamente nas views para evitar lógica de apresentação espalhada pelo código.

---

## 7. Views

### welcome.blade.php — MODIFICADO (substituído completamente)

**Arquivo:** `resources/views/welcome.blade.php`

A view padrão do Laravel (página de boas-vindas com links para documentação) foi **completamente substituída** pela interface da PokéDex.

#### Seções da página

| Seção | Descrição |
|---|---|
| **Header** | Título "PokéDex" com fonte pixel, decoração com SVG de Pokébola |
| **Busca** | Campo de texto + botão de pesquisa (POST para `/pokemon/buscar`) |
| **Aleatório** | Link GET para `/pokemon/aleatorio` com feedback de carregamento |
| **Mensagem de erro** | Exibida via `session('error')` quando Pokémon não é encontrado |
| **Card do Pokémon** | Exibido quando `$pokemon` está definido na view |
| **Mensagem de sucesso** | Exibida via `session('success')` após criar/remover Pokémon |
| **Meus Pokémons** | Grid com cards dos Pokémons do banco + botão "Criar Pokémon" |

#### Card do Pokémon (seção @isset($pokemon))

O card é **idêntico** tanto para Pokémons da PokeAPI quanto para Pokémons do banco local, porque ambos chegam à view pelo mesmo array formatado:

```php
[
    'id'        => int,
    'name'      => string,
    'height'    => int,      // em decímetros (×10)
    'weight'    => int,      // em hectogramas (×10)
    'image'     => string,   // URL da imagem
    'types'     => array,    // ['water', 'rock']
    'abilities' => array,    // ['Solid Rock', 'Hydration']
    'stats'     => array,    // [['name'=>'hp','value'=>80], ...]
]
```

#### Cores dos tipos (definidas inline na view)

18 tipos de Pokémon têm cores específicas definidas em um array PHP na view. A cor do tipo primário define o gradiente do cabeçalho do card e a borda colorida.

#### Tecnologias usadas na view

- **Tailwind CSS** via CDN (`https://cdn.tailwindcss.com`) — sem necessidade de build
- **Google Fonts** — fonte "Press Start 2P" (estilo pixel) para o título, "Inter" para o corpo
- **JavaScript vanilla** — feedback de carregamento nos botões e preview de imagem no formulário

---

### pokemon/create.blade.php — CRIADO

**Arquivo:** `resources/views/pokemon/create.blade.php`

Formulário completo para cadastro de Pokémon próprio. Recursos:

- Upload de imagem com preview em tempo real (via FileReader API)
- Seletores de Tipo 1 e Tipo 2 (18 tipos disponíveis)
- Campos numéricos para altura e peso
- 6 campos de estatísticas com barras animadas em tempo real (`oninput`)
- Exibição de erros de validação do Laravel
- Botão de cancelar (volta para home) e botão de salvar

---

## 8. Seeder

### MeusPokemonsSeeder — CRIADO

**Arquivo:** `database/seeders/MeusPokemonsSeeder.php`

Popula a tabela `meus_pokemons` com os 3 Pokémons criados para o projeto. Executa `truncate()` antes de inserir para evitar duplicatas ao rodar novamente.

```bash
# Comando para executar:
php artisan db:seed --class=MeusPokemonsSeeder
```

#### Pokémons inseridos

| Nome | Tipos | HP | Ataque | Defesa | Atq.Esp | Def.Esp | Vel |
|---|---|---|---|---|---|---|---|
| Aethelfin | water, rock | 80 | 65 | 95 | 80 | 70 | 50 |
| Foliagrove | grass, ground | 75 | 90 | 85 | 55 | 70 | 45 |
| Galehawk | flying, electric | 70 | 95 | 60 | 75 | 65 | 110 |

---

## 9. Storage de Imagens

O Laravel possui um sistema de armazenamento de arquivos baseado em "discos". O disco `public` salva arquivos em `storage/app/public/` e os expõe via URL pública.

### Como funciona

```
[Upload pelo usuário]
        │
        ▼
MeuPokemonController@store
$request->file('imagem')->store('pokemon-images', 'public')
        │
        ▼
storage/app/public/pokemon-images/nome-do-arquivo.png   ← arquivo físico
        │
        ▼
[Symlink criado por: php artisan storage:link]
        │
        ▼
public/storage/ → aponta para → storage/app/public/
        │
        ▼
URL pública: /storage/pokemon-images/nome-do-arquivo.png
        │
        ▼
[Gerada no código por: Storage::url($mp->imagem)]
```

### Imagens dos Pokémons iniciais

As imagens foram copiadas manualmente da pasta `Meus_Pokemons/` para `storage/app/public/pokemon-images/` via PowerShell, simulando o que o upload faria automaticamente:

```
Meus_Pokemons/Aethelfin.png  →  storage/app/public/pokemon-images/Aethelfin.png
Meus_Pokemons/Foliagrove.png →  storage/app/public/pokemon-images/Foliagrove.png
Meus_Pokemons/Galehawk.png   →  storage/app/public/pokemon-images/Galehawk.png
```

---

## 10. Configuração do Ambiente (.env)

O arquivo `.env` **não vai para controle de versão** (está no `.gitignore`). Ele define variáveis de ambiente que o Laravel usa via `config()` e `env()`.

### Configurações relevantes do projeto

```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=PokemonTB
DB_USERNAME=root
DB_PASSWORD=senaisp

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

---

## 11. Fluxo Completo da Aplicação

### Fluxo 1 — Usuário acessa a página inicial

```
Navegador GET /
    → routes/web.php reconhece a rota 'home'
    → PokemonController@index()
        → MeuPokemon::latest()->get()   [consulta banco MySQL]
    → view('welcome', ['meusPokemons' => $collection])
    → welcome.blade.php renderiza header + busca + grid de Pokémons do banco
```

### Fluxo 2 — Busca por um Pokémon do banco (ex: "Aethelfin")

```
Usuário digita "Aethelfin" e clica em Buscar
    → POST /pokemon/buscar
    → PokemonController@search()
        → normaliza: "aethelfin"
        → MeuPokemon::whereRaw('LOWER(nome) = ?', ['aethelfin'])->first()
            → ENCONTROU registro no banco
        → formatLocal($local) converte model → array padrão
            → Storage::url('pokemon-images/Aethelfin.png') = '/storage/pokemon-images/Aethelfin.png'
    → view('welcome', ['pokemon' => $array, 'meusPokemons' => $collection])
    → Card exibido com imagem do storage local
```

### Fluxo 3 — Busca por um Pokémon da API (ex: "pikachu")

```
Usuário digita "pikachu" e clica em Buscar
    → POST /pokemon/buscar
    → PokemonController@search()
        → normaliza: "pikachu"
        → MeuPokemon::whereRaw('LOWER(nome) = ?', ['pikachu'])->first()
            → NÃO ENCONTROU no banco
        → Http::get('https://pokeapi.co/api/v2/pokemon/pikachu')
            → SUCESSO: retorna JSON da PokeAPI
        → format($response->json()) converte JSON → array padrão
    → view('welcome', ['pokemon' => $array, 'meusPokemons' => $collection])
    → Card exibido com imagem do artwork oficial da PokeAPI
```

### Fluxo 4 — Criar um novo Pokémon

```
Usuário clica em "+ Criar Pokémon"
    → GET /meu-pokemon/criar
    → MeuPokemonController@create()
    → view('pokemon.create')  [formulário]

Usuário preenche o formulário e clica em "Salvar Pokémon"
    → POST /meu-pokemon (multipart/form-data)
    → MeuPokemonController@store()
        → $request->validate([...])    [validação servidor]
        → monta string de tipos: "fire,water"
        → $request->file('imagem')->store('pokemon-images', 'public')
            → salva em storage/app/public/pokemon-images/
        → MeuPokemon::create([...])    [INSERT no banco]
    → redirect()->route('home')->with('success', '...')
    → welcome.blade.php exibe mensagem de sucesso + novo card no grid
```

### Fluxo 5 — Remover um Pokémon

```
Usuário clica no botão ✕ e confirma o diálogo
    → DELETE /meu-pokemon/{id}
    → MeuPokemonController@destroy($meuPokemon)
        → Laravel injeta automaticamente o model pelo ID (Route Model Binding)
        → Storage::disk('public')->delete($meuPokemon->imagem)  [apaga arquivo]
        → $meuPokemon->delete()   [DELETE no banco]
    → redirect()->route('home')->with('success', '...')
```

---

## 12. Comunicação entre os Componentes

```
┌─────────────────────────────────────────────────────────────────────┐
│                          NAVEGADOR (Cliente)                        │
│    Envia requisições HTTP (GET/POST/DELETE) para o servidor         │
└───────────────────────────────┬─────────────────────────────────────┘
                                │ HTTP
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        routes/web.php                               │
│    Mapeia URL + método HTTP → Controller@método                     │
│    Aplica middleware 'web' (CSRF, sessões, cookies)                 │
└────────────┬───────────────────────────────────┬────────────────────┘
             │                                   │
             ▼                                   ▼
┌────────────────────────┐         ┌──────────────────────────┐
│   PokemonController    │         │  MeuPokemonController    │
│                        │         │                          │
│  index()               │         │  create()                │
│  search()   ◄──────────┼─────────┼── Busca local primeiro   │
│  random()              │         │  store()                 │
│  format()              │         │  destroy()               │
│  formatLocal()         │         │                          │
└─────┬──────────────────┘         └────────────┬─────────────┘
      │                                          │
      │ usa                                      │ usa
      ▼                                          ▼
┌─────────────────┐                  ┌──────────────────────┐
│  MeuPokemon     │                  │  Storage (Disco       │
│  (Model)        │                  │  'public')            │
│                 │                  │                       │
│  tiposArray()   │                  │  store() → salva      │
│  stats()        │                  │  delete() → apaga     │
│  fillable       │                  │  url() → gera URL     │
└────────┬────────┘                  └──────────┬────────────┘
         │                                       │
         │ Eloquent ORM                           │ Filesystem
         ▼                                       ▼
┌─────────────────┐                  ┌──────────────────────┐
│  MySQL          │                  │  storage/app/public/ │
│  PokemonTB      │                  │  pokemon-images/     │
│  meus_pokemons  │                  │  *.png               │
└─────────────────┘                  └──────────────────────┘
         │                                       │
         │                                       │ symlink
         │                                       ▼
         │                            ┌──────────────────────┐
         │                            │  public/storage/     │
         │                            │  (acessível via HTTP)│
         │                            └──────────────────────┘
         │
         │  Http::get()
         ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     PokeAPI (API externa)                           │
│              https://pokeapi.co/api/v2/pokemon/{name}              │
│         Retorna JSON com sprites, tipos, stats, habilidades        │
└─────────────────────────────────────────────────────────────────────┘
         │
         │ JSON normalizado por format()
         ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        Views (Blade)                                │
│                                                                     │
│  welcome.blade.php          pokemon/create.blade.php               │
│  ├── Header + busca         ├── Formulário de criação              │
│  ├── Card do Pokémon        ├── Preview de imagem (JS)             │
│  └── Grid Meus Pokémons     └── Barras de stat animadas (JS)       │
│                                                                     │
│  Tailwind CSS (CDN) + Google Fonts + JavaScript vanilla            │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 13. O que Existia × O que Foi Criado

### Arquivos MANTIDOS da base Laravel

| Arquivo | Motivo |
|---|---|
| `app/Http/Controllers/Controller.php` | Classe base herdada por todos os controllers |
| `app/Models/User.php` | Model padrão (não usado ativamente, mas parte do framework) |
| `app/Providers/AppServiceProvider.php` | Provider padrão sem customizações |
| `routes/api.php` | Rotas de exemplo com dummyjson.com (exercício anterior) |
| `routes/console.php` | Comando `inspire` padrão |
| `database/migrations/0001_*` | Migrations de users, cache e jobs |
| `database/seeders/DatabaseSeeder.php` | Seeder padrão (não alterado) |
| `resources/css/app.css` | CSS com Tailwind v4 via Vite |
| `resources/js/app.js` | Arquivo JS vazio |
| `vite.config.js` | Configuração do Vite |
| `composer.json` / `package.json` | Dependências sem alteração |

### Arquivos MODIFICADOS

| Arquivo | O que mudou |
|---|---|
| `routes/web.php` | Adicionadas 6 rotas (3 PokeAPI + 3 CRUD) |
| `resources/views/welcome.blade.php` | Substituído completamente pela interface da PokéDex |
| `.env` | Banco trocado para MySQL, session/cache para file |

### Arquivos CRIADOS

| Arquivo | Função |
|---|---|
| `app/Http/Controllers/PokemonController.php` | Consulta PokeAPI e banco local |
| `app/Http/Controllers/MeuPokemonController.php` | CRUD de Pokémons próprios |
| `app/Models/MeuPokemon.php` | Model com helpers de tipos e stats |
| `database/migrations/2026_05_05_*_create_meus_pokemons_table.php` | Estrutura da tabela |
| `database/seeders/MeusPokemonsSeeder.php` | Dados iniciais dos 3 Pokémons |
| `resources/views/pokemon/create.blade.php` | Formulário de cadastro |
| `storage/app/public/pokemon-images/*.png` | Imagens servidas publicamente |
| `public/storage` | Symlink criado por `artisan storage:link` |

---

## Comandos Artisan Utilizados

```bash
# Criar a migration da tabela
php artisan make:migration create_meus_pokemons_table

# Criar o model
php artisan make:model MeuPokemon

# Executar a migration (somente a nova tabela)
php artisan migrate --path=database/migrations/2026_05_05_173917_create_meus_pokemons_table.php

# Criar o symlink de storage público
php artisan storage:link

# Criar o seeder
php artisan make:seeder MeusPokemonsSeeder

# Executar o seeder
php artisan db:seed --class=MeusPokemonsSeeder

# Limpar cache de configuração após alterar .env
php artisan config:clear

# Listar todas as rotas registradas
php artisan route:list
```

---

*Documentação gerada em 05/05/2026*
