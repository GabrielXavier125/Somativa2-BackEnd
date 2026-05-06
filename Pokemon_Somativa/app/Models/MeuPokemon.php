<?php

/*
|--------------------------------------------------------------------------
| ARQUIVO: app/Models/MeuPokemon.php
|--------------------------------------------------------------------------
| Este é o MODEL da tabela 'meus_pokemons' no banco de dados MySQL.
|
| O que é um Model no Laravel?
|  Um Model é a representação de uma tabela do banco em forma de classe PHP.
|  Cada instância do Model representa uma LINHA da tabela.
|  O Laravel usa o Eloquent ORM (Object-Relational Mapping) para traduzir
|  operações PHP em comandos SQL automaticamente.
|
| CONEXÕES DESTE ARQUIVO:
|  → database/migrations/..._create_meus_pokemons_table.php (define a estrutura da tabela)
|  ← app/Http/Controllers/PokemonController.php     (usa para buscar e listar Pokémons)
|  ← app/Http/Controllers/MeuPokemonController.php  (usa para criar e deletar Pokémons)
|  ← database/seeders/MeusPokemonsSeeder.php        (usa para popular o banco inicial)
|  ← resources/views/welcome.blade.php              (chama tiposArray() e stats() na view)
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model; // Classe base do Eloquent ORM do Laravel

class MeuPokemon extends Model
{
    /*
    | $table define explicitamente o nome da tabela no banco.
    | Por convenção o Laravel esperaria 'meu_pokemons', mas nossa tabela
    | se chama 'meus_pokemons', então precisamos informar manualmente.
    | Conecta com: database/migrations/..._create_meus_pokemons_table.php
    */
    protected $table = 'meus_pokemons';

    /*
    | $fillable é a lista de colunas que podem ser preenchidas em massa.
    | Isso é uma proteção de segurança do Laravel chamada "Mass Assignment Protection".
    | Sem essa lista, MeuPokemon::create([...]) lançaria uma exceção.
    | Cada campo aqui corresponde a uma coluna na tabela meus_pokemons.
    | Conecta com: MeuPokemonController@store → MeuPokemon::create([...])
    */
    protected $fillable = [
        'nome',             // VARCHAR(255) — nome do Pokémon
        'imagem',           // VARCHAR(255) — caminho relativo no storage (ex: pokemon-images/Aethelfin.png)
        'tipos',            // VARCHAR(255) — tipos separados por vírgula (ex: "water,rock")
        'altura',           // DECIMAL(5,2) — altura em metros
        'peso',             // DECIMAL(6,2) — peso em kg
        'hp',               // SMALLINT     — estatística HP (1-255)
        'ataque',           // SMALLINT     — estatística Ataque (1-255)
        'defesa',           // SMALLINT     — estatística Defesa (1-255)
        'ataque_especial',  // SMALLINT     — estatística Ataque Especial (1-255)
        'defesa_especial',  // SMALLINT     — estatística Defesa Especial (1-255)
        'velocidade',       // SMALLINT     — estatística Velocidade (1-255)
        'habilidades',      // VARCHAR(255) — habilidades separadas por vírgula (nullable)
    ];

    /*
    |----------------------------------------------------------------------
    | tiposArray(): array
    |----------------------------------------------------------------------
    | Método auxiliar que converte a string de tipos armazenada no banco
    | em um array PHP, facilitando o uso nas views.
    |
    | Por que isso é necessário?
    |  O banco armazena os tipos como string: "water,rock"
    |  A view precisa iterar sobre os tipos individualmente para exibir
    |  os badges coloridos de cada tipo.
    |
    | Exemplos:
    |  "water,rock"   → ["water", "rock"]
    |  "grass,ground" → ["grass", "ground"]
    |  "flying"       → ["flying"]        (tipo único, sem vírgula)
    |
    | USADO EM:
    |  ← resources/views/welcome.blade.php → $mp->tiposArray()
    |  ← app/Http/Controllers/PokemonController.php → formatLocal()
    */
    public function tiposArray(): array
    {
        // explode(',', $this->tipos) → divide a string pela vírgula em array
        // array_map('trim', ...) → remove espaços extras de cada elemento
        // array_filter(...) → remove elementos vazios (caso a string esteja vazia)
        return array_filter(array_map('trim', explode(',', $this->tipos)));
    }

    /*
    |----------------------------------------------------------------------
    | stats(): array
    |----------------------------------------------------------------------
    | Método auxiliar que retorna as 6 estatísticas do Pokémon em formato
    | de array estruturado, pronto para ser iterado na view.
    |
    | Por que isso é necessário?
    |  As stats estão em colunas separadas no banco (hp, ataque, defesa...).
    |  A view do grid de "Meus Pokémons" precisa exibir todas as stats
    |  em um loop, então é mais prático agrupá-las em um array.
    |
    | O campo 'name' usa os nomes em inglês da PokeAPI para manter
    | compatibilidade com o sistema de cores das stats definido na view.
    |
    | USADO EM:
    |  ← resources/views/welcome.blade.php → $mp->stats() no grid de cards
    */
    public function stats(): array
    {
        return [
            // 'name'  → identificador interno (compatível com a PokeAPI e com $statColors da view)
            // 'label' → texto exibido na interface em português
            // 'value' → valor numérico da estatística (lido diretamente do banco)
            ['name' => 'hp',              'label' => 'HP',        'value' => $this->hp],
            ['name' => 'attack',          'label' => 'Ataque',    'value' => $this->ataque],
            ['name' => 'defense',         'label' => 'Defesa',    'value' => $this->defesa],
            ['name' => 'special-attack',  'label' => 'Atq. Esp.', 'value' => $this->ataque_especial],
            ['name' => 'special-defense', 'label' => 'Def. Esp.', 'value' => $this->defesa_especial],
            ['name' => 'speed',           'label' => 'Velocidade','value' => $this->velocidade],
        ];
    }
}
