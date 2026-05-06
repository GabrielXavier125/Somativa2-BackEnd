<?php

/*
|--------------------------------------------------------------------------
| ARQUIVO: app/Http/Controllers/PokemonController.php
|--------------------------------------------------------------------------
| Controller responsável por gerenciar CONSULTAS de Pokémon.
| Ele decide de onde virá a informação: banco de dados local ou PokeAPI.
|
| CONEXÕES DESTE ARQUIVO:
|  ← routes/web.php         (as 3 rotas GET/ POST/pokemon/buscar GET/pokemon/aleatorio)
|  → app/Models/MeuPokemon  (consulta o banco local antes de chamar a API)
|  → PokeAPI externa         (https://pokeapi.co/api/v2/pokemon/{name})
|  → resources/views/welcome.blade.php (retorna dados para a view principal)
|  → Facades\Http            (cliente HTTP do Laravel para chamar APIs externas)
|  → Facades\Storage         (gera URLs públicas das imagens do storage)
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\MeuPokemon;           // Model do banco local — tabela meus_pokemons
use Illuminate\Http\Request;         // Representa a requisição HTTP recebida (dados do formulário, etc.)
use Illuminate\Support\Facades\Http; // Facade para fazer requisições HTTP externas (PokeAPI)
use Illuminate\Support\Facades\Storage; // Facade para manipular arquivos no storage

class PokemonController extends Controller
{
    /*
    |----------------------------------------------------------------------
    | index()
    | Rota: GET /  →  Nome: 'home'
    |----------------------------------------------------------------------
    | Método chamado quando o usuário acessa a página inicial.
    | Carrega todos os Pokémons cadastrados no banco e envia para a view.
    |
    | FLUXO:
    |  1. Consulta a tabela 'meus_pokemons' via Model MeuPokemon
    |  2. Ordena do mais recente para o mais antigo (latest = ORDER BY created_at DESC)
    |  3. Envia a coleção para welcome.blade.php como variável $meusPokemons
    */
    public function index()
    {
        // MeuPokemon::latest()->get() → SELECT * FROM meus_pokemons ORDER BY created_at DESC
        // compact('meusPokemons') → cria array ['meusPokemons' => $collection] para a view
        $meusPokemons = MeuPokemon::latest()->get();
        return view('welcome', compact('meusPokemons'));
    }

    /*
    |----------------------------------------------------------------------
    | search(Request $request)
    | Rota: POST /pokemon/buscar  →  Nome: 'pokemon.search'
    |----------------------------------------------------------------------
    | Método chamado quando o usuário submete o formulário de busca.
    | Implementa uma PRIORIDADE DE BUSCA: banco local primeiro, API depois.
    |
    | FLUXO:
    |  1. Lê e normaliza o nome digitado
    |  2. Busca na tabela meus_pokemons (case-insensitive)
    |     → SE ENCONTRAR: converte via formatLocal() e exibe
    |     → SE NÃO ENCONTRAR: consulta a PokeAPI
    |        → SE SUCESSO: converte via format() e exibe
    |        → SE FALHA: retorna mensagem de erro via session('error')
    */
    public function search(Request $request)
    {
        // Lê o campo 'name' enviado pelo formulário, remove espaços extras
        $input = trim($request->input('name', ''));

        // Converte para minúsculo para padronizar a busca
        $name  = strtolower($input);

        // Se o campo veio vazio, redireciona para a página inicial
        if (empty($name)) {
            return redirect()->route('home');
        }

        // Carrega os Pokémons do banco (sempre necessário para o grid da home)
        $meusPokemons = MeuPokemon::latest()->get();

        // ----- ETAPA 1: Busca no banco de dados local -----
        // whereRaw('LOWER(nome) = ?', [$name]) → busca case-insensitive
        // Garante que "Aethelfin", "aethelfin" e "AETHELFIN" funcionem igualmente
        // Conecta com: app/Models/MeuPokemon.php → tabela MySQL meus_pokemons
        $local = MeuPokemon::whereRaw('LOWER(nome) = ?', [$name])->first();

        if ($local) {
            // Pokémon encontrado no banco local
            // Converte o Model para o array padrão que a view espera
            $pokemon = $this->formatLocal($local);
            return view('welcome', compact('pokemon', 'meusPokemons'));
        }

        // ----- ETAPA 2: Busca na PokeAPI (API externa) -----
        // Http::get() → faz uma requisição GET para a URL informada
        // Conecta com: https://pokeapi.co/api/v2/pokemon/{name}
        $response = Http::get("https://pokeapi.co/api/v2/pokemon/{$name}");

        // failed() retorna true se o status HTTP for 4xx ou 5xx (ex: 404 = não encontrado)
        if ($response->failed()) {
            // withInput() → preserva o valor digitado no campo de busca
            // with('error', ...) → armazena a mensagem na sessão para exibir na view
            return back()->withInput()->with('error', "Pokémon \"{$input}\" não encontrado. Verifique o nome e tente novamente.");
        }

        // Converte o JSON da PokeAPI para o array padrão que a view espera
        $pokemon = $this->format($response->json());
        return view('welcome', compact('pokemon', 'meusPokemons'));
    }

    /*
    |----------------------------------------------------------------------
    | random()
    | Rota: GET /pokemon/aleatorio  →  Nome: 'pokemon.random'
    |----------------------------------------------------------------------
    | Gera um número aleatório entre 1 e 1025 (total de Pokémons na PokeAPI)
    | e busca os dados desse Pokémon diretamente na API externa.
    */
    public function random()
    {
        // rand(1, 1025) → gera ID aleatório correspondente a um Pokémon real na PokeAPI
        $response = Http::get('https://pokeapi.co/api/v2/pokemon/' . rand(1, 1025));

        if ($response->failed()) {
            return back()->with('error', 'Erro ao buscar Pokémon aleatório. Tente novamente.');
        }

        // Converte o JSON da API para o array padrão da view
        $pokemon      = $this->format($response->json());

        // Também carrega os Pokémons do banco para manter o grid visível na home
        $meusPokemons = MeuPokemon::latest()->get();

        return view('welcome', compact('pokemon', 'meusPokemons'));
    }

    /*
    |----------------------------------------------------------------------
    | formatLocal(MeuPokemon $mp)  [MÉTODO PRIVADO]
    |----------------------------------------------------------------------
    | Converte um registro do banco de dados (Model MeuPokemon) para o
    | mesmo formato de array que a view welcome.blade.php espera receber.
    |
    | Por que existe este método?
    |  O banco armazena altura em METROS e peso em KG.
    |  A view espera height em DECÍMETROS e weight em HECTOGRAMAS
    |  (mesma escala que a PokeAPI usa) para que a conversão na view seja igual.
    |
    | CONEXÕES:
    |  ← app/Models/MeuPokemon.php (recebe o model como parâmetro)
    |  ← MeuPokemon::tiposArray()  (método do model que divide a string de tipos)
    |  → Storage::url()            (gera a URL pública da imagem no storage)
    |  → welcome.blade.php         (o array retornado alimenta o card do Pokémon)
    */
    private function formatLocal(MeuPokemon $mp): array
    {
        // Divide a string de habilidades "Solid Rock, Hydration" em array ["Solid Rock", "Hydration"]
        // Se não houver habilidades cadastradas, retorna array vazio
        $habilidades = $mp->habilidades
            ? array_map('trim', explode(',', $mp->habilidades))
            : [];

        return [
            'id'        => $mp->id,

            // 'name' (em inglês) pois a view usa $pokemon['name'] para ambos os casos
            'name'      => $mp->nome,

            // Converte metros → decímetros (×10): ex: 1.3m → 13 dm
            // A view divide por 10 para exibir em metros, então o resultado final é correto
            'height'    => (int) round($mp->altura * 10),

            // Converte kg → hectogramas (×10): ex: 52.0kg → 520 hg
            'weight'    => (int) round($mp->peso  * 10),

            // Storage::url() gera a URL pública da imagem
            // Ex: 'pokemon-images/Aethelfin.png' → '/storage/pokemon-images/Aethelfin.png'
            // Funciona graças ao symlink criado por: php artisan storage:link
            // Conecta com: storage/app/public/pokemon-images/ e public/storage/
            'image'     => $mp->imagem ? Storage::url($mp->imagem) : null,

            // tiposArray() está definido em app/Models/MeuPokemon.php
            // Divide "water,rock" em ["water", "rock"]
            'types'     => $mp->tiposArray(),

            'abilities' => $habilidades,

            // As 6 estatísticas no mesmo formato que a PokeAPI retorna
            // A view itera sobre este array para renderizar as barras de stats
            'stats'     => [
                ['name' => 'hp',              'value' => $mp->hp],
                ['name' => 'attack',          'value' => $mp->ataque],
                ['name' => 'defense',         'value' => $mp->defesa],
                ['name' => 'special-attack',  'value' => $mp->ataque_especial],
                ['name' => 'special-defense', 'value' => $mp->defesa_especial],
                ['name' => 'speed',           'value' => $mp->velocidade],
            ],
        ];
    }

    /*
    |----------------------------------------------------------------------
    | format(array $data)  [MÉTODO PRIVADO]
    |----------------------------------------------------------------------
    | Converte o JSON bruto da PokeAPI para o mesmo array padrão que
    | formatLocal() retorna. Isso garante que a view welcome.blade.php
    | funcione de forma idêntica para Pokémons da API e do banco local.
    |
    | A PokeAPI retorna uma estrutura JSON complexa e aninhada.
    | Este método "achata" essa estrutura em um array simples.
    |
    | CONEXÕES:
    |  ← Http::get() response da PokeAPI (recebe o JSON decodificado)
    |  → welcome.blade.php (o array retornado alimenta o card do Pokémon)
    */
    private function format(array $data): array
    {
        return [
            'id'   => $data['id'],    // Número do Pokémon na Pokédex (ex: 25 = Pikachu)
            'name' => $data['name'],  // Nome em inglês (ex: 'pikachu')

            // height vem em decímetros na PokeAPI (ex: 4 = 0.4m)
            // A view divide por 10 para exibir em metros
            'height' => $data['height'],

            // weight vem em hectogramas na PokeAPI (ex: 60 = 6.0kg)
            // A view divide por 10 para exibir em kg
            'weight' => $data['weight'],

            // Tenta pegar o artwork oficial (imagem de alta qualidade)
            // Se não existir, usa o sprite padrão (pixel art menor)
            'image' => $data['sprites']['other']['official-artwork']['front_default']
                    ?? $data['sprites']['front_default'],

            // $data['types'] é um array de objetos: [{"slot":1,"type":{"name":"fire",...}},...]
            // array_map extrai apenas o nome de cada tipo → ['fire', 'flying']
            'types' => array_map(fn($t) => $t['type']['name'], $data['types']),

            // $data['abilities'] é array de objetos: [{"ability":{"name":"blaze",...},...},...]
            // array_map extrai apenas o nome de cada habilidade → ['blaze', 'solar-power']
            'abilities' => array_map(fn($a) => $a['ability']['name'], $data['abilities']),

            // $data['stats'] é array de objetos com nome e valor base
            // array_map simplifica para [['name'=>'hp','value'=>45], ...]
            'stats' => array_map(fn($s) => [
                'name'  => $s['stat']['name'],  // Ex: 'hp', 'attack', 'speed'
                'value' => $s['base_stat'],      // Ex: 45, 49, 65
            ], $data['stats']),
        ];
    }
}
