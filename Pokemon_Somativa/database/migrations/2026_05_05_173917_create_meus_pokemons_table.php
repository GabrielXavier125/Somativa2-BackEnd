<?php

/*
|--------------------------------------------------------------------------
| ARQUIVO: database/migrations/2026_05_05_173917_create_meus_pokemons_table.php
|--------------------------------------------------------------------------
| Este é o arquivo de MIGRATION da tabela 'meus_pokemons'.
|
| O que é uma Migration?
|  É um arquivo PHP que descreve a ESTRUTURA de uma tabela do banco.
|  Em vez de criar a tabela manualmente no MySQL, escrevemos PHP e o
|  Laravel executa o SQL correspondente via: php artisan migrate
|
|  Isso permite que qualquer pessoa que clone o projeto possa recriar
|  o banco de dados exatamente igual com um único comando.
|
| EXECUTADO COM:
|  php artisan migrate
|  (ou com --path para rodar só este arquivo específico)
|
| CONEXÕES DESTE ARQUIVO:
|  → Banco de dados MySQL (PokemonTB) — cria a tabela fisicamente
|  ← app/Models/MeuPokemon.php       — o Model usa esta tabela
|  ← .env                            — define qual banco conectar (DB_DATABASE=PokemonTB)
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;   // Classe para definir colunas da tabela
use Illuminate\Support\Facades\Schema;      // Facade para executar operações de schema no banco

// Classe anônima que estende Migration — padrão do Laravel para migrations modernas
return new class extends Migration
{
    /*
    |----------------------------------------------------------------------
    | up()
    | Executado quando rodamos: php artisan migrate
    | Cria a tabela 'meus_pokemons' no banco de dados.
    |----------------------------------------------------------------------
    */
    public function up(): void
    {
        // Schema::create() → executa CREATE TABLE meus_pokemons no MySQL
        Schema::create('meus_pokemons', function (Blueprint $table) {

            // id() → coluna 'id' BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            // Gerada automaticamente e incrementada a cada novo registro
            $table->id();

            // Nome do Pokémon — campo obrigatório (sem nullable)
            // Usado na busca por LOWER(nome) no PokemonController@search
            $table->string('nome');

            // Caminho relativo da imagem no storage
            // Ex: 'pokemon-images/Aethelfin.png'
            // nullable() → permite que o Pokémon não tenha imagem
            // Conecta com: storage/app/public/pokemon-images/
            $table->string('imagem')->nullable();

            // Tipos do Pokémon armazenados como string separada por vírgula
            // Ex: 'water,rock' ou 'grass,ground' ou apenas 'fire'
            // default('normal') → se não informado, será 'normal'
            // Convertido em array pelo método tiposArray() do Model
            $table->string('tipos')->default('normal');

            // Altura em metros com 2 casas decimais (ex: 1.30, 0.90)
            // DECIMAL(5,2) → até 999.99 metros (mais que suficiente)
            $table->decimal('altura', 5, 2)->default(0);

            // Peso em quilogramas com 2 casas decimais (ex: 52.00, 28.50)
            // DECIMAL(6,2) → até 9999.99 kg
            $table->decimal('peso', 6, 2)->default(0);

            // As 6 estatísticas base — SMALLINT UNSIGNED suporta 0 a 65535
            // Na prática limitadas a 1–255 pela validação do Controller
            // default(45) → valor médio padrão caso não informado
            $table->unsignedSmallInteger('hp')->default(45);
            $table->unsignedSmallInteger('ataque')->default(45);
            $table->unsignedSmallInteger('defesa')->default(45);
            $table->unsignedSmallInteger('ataque_especial')->default(45);
            $table->unsignedSmallInteger('defesa_especial')->default(45);
            $table->unsignedSmallInteger('velocidade')->default(45);

            // Habilidades como string separada por vírgula
            // Ex: 'Solid Rock, Hydration'
            // nullable() → campo opcional no formulário de criação
            $table->string('habilidades')->nullable();

            // timestamps() → cria duas colunas automaticamente:
            //  'created_at' TIMESTAMP → data/hora de criação do registro
            //  'updated_at' TIMESTAMP → data/hora da última atualização
            // O Eloquent atualiza essas colunas automaticamente
            $table->timestamps();
        });
    }

    /*
    |----------------------------------------------------------------------
    | down()
    | Executado quando rodamos: php artisan migrate:rollback
    | DESFAZ o que o método up() fez — apaga a tabela do banco.
    | Útil para desfazer migrações em caso de erro.
    |----------------------------------------------------------------------
    */
    public function down(): void
    {
        // dropIfExists → DROP TABLE IF EXISTS meus_pokemons
        // Não dá erro se a tabela já não existir
        Schema::dropIfExists('meus_pokemons');
    }
};
