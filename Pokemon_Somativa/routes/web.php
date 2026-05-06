<?php

/*
|--------------------------------------------------------------------------
| ARQUIVO: routes/web.php
|--------------------------------------------------------------------------
| Este é o arquivo central de ROTAS WEB da aplicação.
| Toda URL que o usuário acessa no navegador passa por aqui.
|
| O Laravel lê este arquivo e decide qual Controller e qual método
| deve ser executado com base na URL e no método HTTP (GET, POST, DELETE).
|
| CONEXÕES DESTE ARQUIVO:
|  → app/Http/Controllers/PokemonController.php    (consultas de Pokémon)
|  → app/Http/Controllers/MeuPokemonController.php (CRUD de Pokémons próprios)
|  → Middleware 'web': aplica CSRF, sessões e cookies automaticamente
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\PokemonController;
use App\Http\Controllers\MeuPokemonController;
use Illuminate\Support\Facades\Route;

/*
|------------------------------------------------------------------
| GRUPO 1 — Pokédex (consulta à API pública PokeAPI)
|------------------------------------------------------------------
*/

// Página inicial: carrega todos os Pokémons do banco e exibe a home.
// Controller: PokemonController@index → retorna a view 'welcome'
// Nome da rota: 'home' → usado em route('home') nas views
Route::get('/', [PokemonController::class, 'index'])->name('home');

// Busca por nome: recebe o nome digitado no formulário via POST.
// Usa POST para evitar que o nome apareça na URL como parâmetro GET.
// Controller: PokemonController@search → busca banco local, depois PokeAPI
// Nome da rota: 'pokemon.search' → usado em action="{{ route('pokemon.search') }}" no welcome.blade.php
Route::post('/pokemon/buscar', [PokemonController::class, 'search'])->name('pokemon.search');

// Pokémon aleatório: gera um ID aleatório e busca na PokeAPI.
// Usa GET pois não há dados sensíveis sendo enviados.
// Controller: PokemonController@random
// Nome da rota: 'pokemon.random' → usado no botão de aleatório no welcome.blade.php
Route::get('/pokemon/aleatorio', [PokemonController::class, 'random'])->name('pokemon.random');

/*
|------------------------------------------------------------------
| GRUPO 2 — Meus Pokémons (CRUD no banco de dados MySQL)
|------------------------------------------------------------------
*/

// Exibe o formulário de criação de Pokémon.
// Controller: MeuPokemonController@create → retorna a view 'pokemon.create'
// Nome da rota: 'meu-pokemon.create' → usado no botão "+ Criar Pokémon" no welcome.blade.php
Route::get('/meu-pokemon/criar', [MeuPokemonController::class, 'create'])->name('meu-pokemon.create');

// Processa o formulário de criação: valida, salva a imagem e insere no banco.
// Usa POST pois recebe dados do formulário (incluindo upload de imagem).
// Controller: MeuPokemonController@store → INSERT na tabela meus_pokemons
// Nome da rota: 'meu-pokemon.store' → usado no action do <form> em pokemon/create.blade.php
Route::post('/meu-pokemon', [MeuPokemonController::class, 'store'])->name('meu-pokemon.store');

// Remove um Pokémon pelo ID.
// Usa DELETE (método HTTP semântico para exclusão).
// {meuPokemon} é Route Model Binding: o Laravel busca automaticamente o
// registro na tabela meus_pokemons pelo ID passado na URL e injeta o model.
// Controller: MeuPokemonController@destroy → apaga imagem + DELETE no banco
// Nome da rota: 'meu-pokemon.destroy' → usado no form com @method('DELETE') no welcome.blade.php
Route::delete('/meu-pokemon/{meuPokemon}', [MeuPokemonController::class, 'destroy'])->name('meu-pokemon.destroy');
