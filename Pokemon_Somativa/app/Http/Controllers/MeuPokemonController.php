<?php

/*
|--------------------------------------------------------------------------
| ARQUIVO: app/Http/Controllers/MeuPokemonController.php
|--------------------------------------------------------------------------
| Controller responsável pelo CRUD (Create, Read, Update, Delete) dos
| Pokémons cadastrados pelo usuário no banco de dados local.
|
| CRUD implementado neste arquivo:
|  C → create() + store()  (formulário + salvar no banco)
|  R → feito no PokemonController@index (listar) e @search (buscar)
|  U → não implementado (escopo do projeto)
|  D → destroy()           (remover do banco e apagar imagem)
|
| CONEXÕES DESTE ARQUIVO:
|  ← routes/web.php                          (3 rotas: GET criar, POST store, DELETE destroy)
|  → app/Models/MeuPokemon.php               (model que representa a tabela meus_pokemons)
|  → resources/views/pokemon/create.blade.php (formulário de criação)
|  → storage/app/public/pokemon-images/      (pasta onde as imagens são salvas)
|  → Facades\Storage                         (salva e deleta arquivos)
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\MeuPokemon;               // Model conectado à tabela meus_pokemons no MySQL
use Illuminate\Http\Request;             // Classe que representa a requisição HTTP (form, upload, etc.)
use Illuminate\Support\Facades\Storage; // Facade para operações no sistema de arquivos (salvar/deletar)

class MeuPokemonController extends Controller
{
    /*
    |----------------------------------------------------------------------
    | create()
    | Rota: GET /meu-pokemon/criar  →  Nome: 'meu-pokemon.create'
    |----------------------------------------------------------------------
    | Apenas exibe o formulário de criação de Pokémon.
    | Não faz nenhuma operação no banco — só renderiza a view.
    |
    | CONEXÃO:
    |  → resources/views/pokemon/create.blade.php
    */
    public function create()
    {
        $tiposDisponiveis = [
            'normal','fire','water','electric','grass','ice',
            'fighting','poison','ground','flying','psychic','bug',
            'rock','ghost','dragon','dark','steel','fairy',
        ];

        $tiposLabels = [
            'normal'   => 'Normal',   'fire'     => 'Fogo',     'water'   => 'Água',
            'electric' => 'Elétrico', 'grass'    => 'Planta',   'ice'     => 'Gelo',
            'fighting' => 'Lutador',  'poison'   => 'Veneno',   'ground'  => 'Terra',
            'flying'   => 'Voador',   'psychic'  => 'Psíquico', 'bug'     => 'Inseto',
            'rock'     => 'Pedra',    'ghost'    => 'Fantasma', 'dragon'  => 'Dragão',
            'dark'     => 'Sombrio',  'steel'    => 'Aço',      'fairy'   => 'Fada',
        ];

        // view('pokemon.create') → carrega resources/views/pokemon/create.blade.php
        return view('pokemon.create', compact('tiposDisponiveis', 'tiposLabels'));
    }

    /*
    |----------------------------------------------------------------------
    | store(Request $request)
    | Rota: POST /meu-pokemon  →  Nome: 'meu-pokemon.store'
    |----------------------------------------------------------------------
    | Processa o formulário de criação. Executa 4 etapas em ordem:
    |  1. Valida os dados enviados pelo formulário
    |  2. Monta a string de tipos (ex: "fire,water")
    |  3. Salva a imagem no storage (se houver)
    |  4. Insere o registro no banco de dados
    |
    | CONEXÕES:
    |  ← resources/views/pokemon/create.blade.php (dados do formulário)
    |  → app/Models/MeuPokemon.php               (INSERT no banco)
    |  → storage/app/public/pokemon-images/      (imagem salva aqui)
    |  → routes/web.php via redirect('home')     (redireciona após salvar)
    */
    public function store(Request $request)
    {
        /*
        | ETAPA 1: Validação dos dados
        | validate() verifica as regras antes de processar qualquer coisa.
        | Se alguma regra falhar, o Laravel redireciona automaticamente de volta
        | ao formulário com os erros disponíveis via $errors na view.
        */
        $data = $request->validate([
            'nome'             => 'required|string|max:100',  // Obrigatório, texto, máx 100 chars
            'imagem'           => 'nullable|image|max:2048',  // Opcional, deve ser imagem, máx 2MB
            'tipo1'            => 'required|string',           // Tipo primário obrigatório
            'tipo2'            => 'nullable|string',           // Tipo secundário opcional
            'altura'           => 'required|numeric|min:0',   // Número positivo
            'peso'             => 'required|numeric|min:0',   // Número positivo
            'hp'               => 'required|integer|min:1|max:255',
            'ataque'           => 'required|integer|min:1|max:255',
            'defesa'           => 'required|integer|min:1|max:255',
            'ataque_especial'  => 'required|integer|min:1|max:255',
            'defesa_especial'  => 'required|integer|min:1|max:255',
            'velocidade'       => 'required|integer|min:1|max:255',
            'habilidades'      => 'nullable|string|max:255',
        ]);

        /*
        | ETAPA 2: Monta a string de tipos
        | O banco armazena os tipos como string separada por vírgula.
        | Ex: tipo1="fire" + tipo2="water" → "fire,water"
        | Se tipo2 for igual a tipo1 ou vazio, armazena apenas tipo1.
        */
        $tipos = $data['tipo1'];
        if (!empty($data['tipo2']) && $data['tipo2'] !== $data['tipo1']) {
            $tipos .= ',' . $data['tipo2'];
        }

        /*
        | ETAPA 3: Upload da imagem (opcional)
        | hasFile('imagem') → verifica se o usuário enviou um arquivo
        | store('pokemon-images', 'public') → salva o arquivo na pasta
        |   pokemon-images dentro do disco 'public'
        |   (= storage/app/public/pokemon-images/)
        | O Laravel gera automaticamente um nome único para o arquivo.
        | store() retorna o caminho relativo: 'pokemon-images/nome-gerado.jpg'
        | Esse caminho é salvo no banco para poder recuperar a imagem depois.
        */
        $imagemPath = null;
        if ($request->hasFile('imagem')) {
            // Salva em: storage/app/public/pokemon-images/
            // Acessível via URL: /storage/pokemon-images/ (graças ao symlink)
            $imagemPath = $request->file('imagem')->store('pokemon-images', 'public');
        }

        /*
        | ETAPA 4: Persistência no banco de dados
        | MeuPokemon::create() → executa INSERT INTO meus_pokemons (...)
        | Só funciona porque os campos estão listados em $fillable no Model.
        | Conecta com: app/Models/MeuPokemon.php → tabela MySQL meus_pokemons
        */
        MeuPokemon::create([
            'nome'             => $data['nome'],
            'imagem'           => $imagemPath,      // Caminho relativo ou null
            'tipos'            => $tipos,            // Ex: "fire,water" ou "grass"
            'altura'           => $data['altura'],
            'peso'             => $data['peso'],
            'hp'               => $data['hp'],
            'ataque'           => $data['ataque'],
            'defesa'           => $data['defesa'],
            'ataque_especial'  => $data['ataque_especial'],
            'defesa_especial'  => $data['defesa_especial'],
            'velocidade'       => $data['velocidade'],
            'habilidades'      => $data['habilidades'] ?? null,
        ]);

        // Redireciona para a página inicial com mensagem de sucesso
        // with('success', ...) → armazena na sessão; exibida em welcome.blade.php
        return redirect()->route('home')->with('success', "Pokémon \"{$data['nome']}\" criado com sucesso!");
    }

    /*
    |----------------------------------------------------------------------
    | destroy(MeuPokemon $meuPokemon)
    | Rota: DELETE /meu-pokemon/{meuPokemon}  →  Nome: 'meu-pokemon.destroy'
    |----------------------------------------------------------------------
    | Remove um Pokémon do banco e apaga sua imagem do storage.
    |
    | ROUTE MODEL BINDING:
    |  O parâmetro {meuPokemon} na URL contém o ID do registro.
    |  O Laravel automaticamente busca MeuPokemon::find($id) e injeta
    |  o model já pronto no método — sem precisar fazer a busca manualmente.
    |
    | CONEXÕES:
    |  ← routes/web.php                        (rota DELETE com {meuPokemon})
    |  ← welcome.blade.php                     (form com @method('DELETE'))
    |  → app/Models/MeuPokemon.php             (model injetado automaticamente)
    |  → storage/app/public/pokemon-images/    (imagem apagada daqui)
    */
    public function destroy(MeuPokemon $meuPokemon)
    {
        // Verifica se o Pokémon tem imagem cadastrada antes de tentar apagar
        if ($meuPokemon->imagem) {
            // Storage::disk('public')->delete() → apaga o arquivo físico do storage
            // Ex: apaga storage/app/public/pokemon-images/Aethelfin.png
            Storage::disk('public')->delete($meuPokemon->imagem);
        }

        // delete() → executa DELETE FROM meus_pokemons WHERE id = ?
        $meuPokemon->delete();

        // Redireciona com mensagem de sucesso exibida em welcome.blade.php
        return redirect()->route('home')->with('success', 'Pokémon removido com sucesso.');
    }
}
