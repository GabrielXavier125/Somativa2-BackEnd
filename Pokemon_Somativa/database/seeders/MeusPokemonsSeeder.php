<?php

/*
|--------------------------------------------------------------------------
| ARQUIVO: database/seeders/MeusPokemonsSeeder.php
|--------------------------------------------------------------------------
| Este é o SEEDER dos Pokémons iniciais do projeto.
|
| O que é um Seeder?
|  É um arquivo PHP que POPULA o banco de dados com dados iniciais.
|  Em vez de inserir os dados manualmente via phpMyAdmin ou SQL,
|  descrevemos os dados em PHP e o Laravel executa o INSERT automaticamente.
|
| EXECUTADO COM:
|  php artisan db:seed --class=MeusPokemonsSeeder
|
| CONEXÕES DESTE ARQUIVO:
|  → app/Models/MeuPokemon.php              (usa o Model para inserir os dados)
|  → Tabela MySQL meus_pokemons             (os registros são inseridos aqui)
|  → storage/app/public/pokemon-images/    (as imagens devem estar nesta pasta)
|    (as imagens foram copiadas manualmente da pasta Meus_Pokemons/ para o storage)
|--------------------------------------------------------------------------
*/

namespace Database\Seeders;

use App\Models\MeuPokemon; // Model que representa a tabela meus_pokemons
use Illuminate\Database\Seeder; // Classe base para todos os seeders do Laravel

class MeusPokemonsSeeder extends Seeder
{
    /*
    |----------------------------------------------------------------------
    | run()
    | Método principal do Seeder — executado pelo comando artisan db:seed
    |----------------------------------------------------------------------
    */
    public function run(): void
    {
        // truncate() → DELETE FROM meus_pokemons (apaga todos os registros)
        // Garante que ao rodar o seeder novamente não crie duplicatas
        // ATENÇÃO: reseta também o auto_increment do ID
        MeuPokemon::truncate();

        // Array com os dados de cada Pokémon a ser inserido
        // Cada chave corresponde a uma coluna da tabela meus_pokemons
        $pokemons = [
            [
                // Pokémon 1: Aethelfin — O Pokémon Recife Flutuante
                'nome'             => 'Aethelfin',
                // Caminho relativo da imagem no storage
                // Arquivo físico em: storage/app/public/pokemon-images/Aethelfin.png
                // URL pública acessível em: /storage/pokemon-images/Aethelfin.png
                'imagem'           => 'pokemon-images/Aethelfin.png',
                // Tipos armazenados como string separada por vírgula
                // Convertidos em array pelo método tiposArray() do Model
                'tipos'            => 'water,rock',
                'altura'           => 1.3,   // metros
                'peso'             => 52.0,  // kg
                // Stats estimadas baseadas no perfil defensivo do Pokémon
                'hp'               => 80,
                'ataque'           => 65,
                'defesa'           => 95,   // Alta defesa — tipo Rocha
                'ataque_especial'  => 80,
                'defesa_especial'  => 70,
                'velocidade'       => 50,   // Baixa velocidade — Pokémon pesado
                'habilidades'      => 'Solid Rock, Hydration',
            ],
            [
                // Pokémon 2: Foliagrove — O Pokémon Guardião do Bosque
                'nome'             => 'Foliagrove',
                'imagem'           => 'pokemon-images/Foliagrove.png',
                'tipos'            => 'grass,ground',
                'altura'           => 1.5,   // metros
                'peso'             => 68.0,  // kg
                // Stats estimadas — perfil físico/territorial
                'hp'               => 75,
                'ataque'           => 90,   // Alto ataque físico
                'defesa'           => 85,
                'ataque_especial'  => 55,   // Baixo ataque especial
                'defesa_especial'  => 70,
                'velocidade'       => 45,   // Lento — típico de Pokémons terrestres pesados
                'habilidades'      => 'Overgrow, Rough Skin',
            ],
            [
                // Pokémon 3: Galehawk — O Pokémon Predador da Tempestade
                'nome'             => 'Galehawk',
                'imagem'           => 'pokemon-images/Galehawk.png',
                'tipos'            => 'flying,electric',
                'altura'           => 0.9,   // metros — porte menor, mais ágil
                'peso'             => 28.5,  // kg — leve para voar em alta velocidade
                // Stats estimadas — perfil veloz e ofensivo
                'hp'               => 70,
                'ataque'           => 95,   // Alto ataque físico — mergulho em alta velocidade
                'defesa'           => 60,   // Defesa baixa — troca por velocidade
                'ataque_especial'  => 75,
                'defesa_especial'  => 65,
                'velocidade'       => 110,  // Altíssima velocidade — voador da tempestade
                'habilidades'      => 'Volt Absorb, Gale Wings',
            ],
        ];

        // Itera sobre o array e insere cada Pokémon no banco
        // MeuPokemon::create($dados) → INSERT INTO meus_pokemons (...) VALUES (...)
        // Só funciona porque os campos estão no $fillable do Model
        foreach ($pokemons as $dados) {
            MeuPokemon::create($dados);
        }
    }
}
