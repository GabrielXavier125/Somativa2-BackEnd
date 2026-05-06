{{--
|--------------------------------------------------------------------------
| ARQUIVO: resources/views/welcome.blade.php
|--------------------------------------------------------------------------
| Esta é a VIEW PRINCIPAL da aplicação — a página que o usuário vê ao acessar /.
|
| O que é uma View Blade?
|  É um arquivo HTML com sintaxe especial do Laravel (Blade).
|  O Laravel processa as diretivas Blade ({{ }}, @if, @foreach...) e
|  gera o HTML final que o navegador recebe.
|
| DADOS RECEBIDOS DO CONTROLLER (via compact()):
|  $pokemon      → array com dados de um Pokémon (quando há busca ativa)
|                  vem de PokemonController@search ou @random
|                  pode ser da PokeAPI ou do banco local — mesmo formato
|  $meusPokemons → coleção de todos os registros da tabela meus_pokemons
|                  vem de PokemonController@index, @search ou @random
|
| CONEXÕES DESTE ARQUIVO:
|  ← app/Http/Controllers/PokemonController.php  (envia $pokemon e $meusPokemons)
|  → routes/web.php via route()                  (formulário e links usam nomes de rota)
|  → app/Models/MeuPokemon.php                   ($mp->tiposArray(), $mp->stats())
|  → storage/app/public/                         (Storage::url() gera URL das imagens)
|  → CDN Tailwind CSS                            (estilização via classes utilitárias)
|  → Google Fonts                                (fontes Press Start 2P e Inter)
|--------------------------------------------------------------------------
--}}
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PokéDex</title>

    {{-- Tailwind CSS via CDN: carrega o framework de CSS diretamente da internet
         Dispensa a necessidade de rodar npm run dev ou npm run build --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Google Fonts: carrega duas fontes externas
         Press Start 2P → fonte estilo pixel/videogame para o título
         Inter           → fonte moderna para o restante do texto --}}
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        {{-- Configuração do Tailwind: adiciona as fontes customizadas como classes
             font-pixel → aplica a fonte "Press Start 2P" (usada no título PokéDex)
             font-sans  → aplica a fonte "Inter" (padrão para textos) --}}
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        pixel: ['"Press Start 2P"', 'cursive'],
                        sans:  ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        /* Define Inter como fonte padrão do corpo da página */
        body { font-family: 'Inter', sans-serif; }

        /* Classe .pokeball-bg: adiciona uma Pokébola decorativa como
           imagem de fundo SVG embutida (data URI — não precisa de arquivo externo)
           Usada no cabeçalho da página */
        .pokeball-bg {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='48' fill='none' stroke='rgba(255,255,255,0.06)' stroke-width='2'/%3E%3Cline x1='2' y1='50' x2='98' y2='50' stroke='rgba(255,255,255,0.06)' stroke-width='2'/%3E%3Ccircle cx='50' cy='50' r='12' fill='none' stroke='rgba(255,255,255,0.06)' stroke-width='2'/%3E%3C/svg%3E");
            background-size: 280px 280px;
            background-position: right -40px top -40px;
            background-repeat: no-repeat;
        }

        /* .stat-bar: animação de preenchimento das barras de estatísticas
           A largura é definida inline via style="width: X%"
           A transição cria o efeito de "crescimento" ao carregar a página */
        .stat-bar { transition: width 0.8s ease-out; }

        /* .card-enter: animação de entrada do card do Pokémon
           O card surge suavemente de baixo para cima ao ser exibido */
        .card-enter { animation: fadeUp 0.4s ease-out; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); } /* começa invisível e abaixo */
            to   { opacity: 1; transform: translateY(0); }    /* termina visível na posição final */
        }
    </style>
</head>

{{-- Fundo vermelho com gradiente diagonal — cor temática da Pokébola --}}
<body class="min-h-screen bg-red-600" style="background: linear-gradient(160deg, #CC0000 0%, #EE1111 40%, #CC0000 100%);">

<div class="min-h-screen px-4 py-10 md:py-16">

    {{-- ===== SEÇÃO 1: CABEÇALHO ===== --}}
    {{-- pokeball-bg: aplica a decoração SVG de Pokébola via CSS --}}
    <div class="text-center mb-10 pokeball-bg">
        {{-- font-pixel: usa a fonte "Press Start 2P" definida no tailwind.config acima --}}
        <h1 class="font-pixel text-yellow-300 text-2xl md:text-3xl tracking-wide drop-shadow-lg">
            PokéDex
        </h1>
        <p class="text-red-200 mt-3 text-sm">Explore o mundo dos Pokémons</p>
    </div>

    {{-- ===== SEÇÃO 2: ÁREA DE BUSCA ===== --}}
    <div class="max-w-md mx-auto">

        {{-- FORMULÁRIO DE BUSCA
             action="{{ route('pokemon.search') }}" → envia para POST /pokemon/buscar
             Definido em routes/web.php → PokemonController@search
             method="POST" → o nome digitado não aparece na URL --}}
        <form action="{{ route('pokemon.search') }}" method="POST" id="search-form">

            {{-- @csrf: diretiva Blade que gera um token de segurança oculto
                 O Laravel verifica este token em toda requisição POST
                 para prevenir ataques CSRF (Cross-Site Request Forgery)
                 Sem o @csrf, a requisição seria bloqueada com erro 419 --}}
            @csrf

            <div class="flex gap-2">
                {{-- Campo de texto onde o usuário digita o nome do Pokémon
                     name="name" → o Controller lê via $request->input('name')
                     value="{{ old('name') }}" → preserva o texto digitado se houver erro de validação
                     autocomplete="off" → evita sugestões automáticas do navegador --}}
                <input
                    type="text"
                    name="name"
                    id="search-input"
                    value="{{ old('name') }}"
                    placeholder="pikachu, charizard, bulbasaur..."
                    autocomplete="off"
                    class="flex-1 px-4 py-3.5 rounded-2xl text-gray-700 text-base outline-none shadow-inner border-4 border-yellow-300 focus:border-yellow-200 placeholder-gray-400"
                >
                {{-- Botão de envio do formulário
                     id="search-btn" → referenciado pelo JavaScript abaixo para feedback de loading --}}
                <button
                    type="submit"
                    id="search-btn"
                    class="bg-yellow-300 hover:bg-yellow-200 active:bg-yellow-400 text-gray-800 font-bold px-5 py-3.5 rounded-2xl shadow-lg transition-all duration-150 border-b-4 border-yellow-500 active:border-b-0 active:translate-y-0.5 text-lg"
                >
                    🔍
                </button>
            </div>
        </form>

        {{-- BOTÃO DE POKÉMON ALEATÓRIO
             href="{{ route('pokemon.random') }}" → aponta para GET /pokemon/aleatorio
             Definido em routes/web.php → PokemonController@random
             id="random-btn" → referenciado pelo JavaScript para feedback de loading --}}
        <a
            href="{{ route('pokemon.random') }}"
            id="random-btn"
            class="mt-3 flex items-center justify-center gap-2 bg-white hover:bg-gray-50 active:bg-gray-100 text-red-600 font-semibold py-3.5 rounded-2xl shadow-lg transition-all duration-150 border-b-4 border-gray-200 active:border-b-0 active:translate-y-0.5"
        >
            <span id="random-icon">🎲</span>
            <span id="random-text">Pokémon Aleatório</span>
        </a>

        {{-- MENSAGEM DE ERRO
             session('error') → lida da sessão, gravada pelo Controller via with('error', '...')
             Exibida quando o Pokémon não é encontrado nem no banco nem na PokeAPI --}}
        @if (session('error'))
            <div class="mt-4 flex items-start gap-3 bg-red-900/80 text-red-100 px-5 py-4 rounded-2xl border border-red-700 text-sm">
                <span class="text-lg">❌</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

    </div>

    {{-- ===== SEÇÃO 3: CARD DO POKÉMON ===== --}}
    {{-- @isset($pokemon) → só renderiza esta seção se o Controller enviou $pokemon
         $pokemon é enviado por PokemonController@search ou @random
         Contém o mesmo formato de array independente da origem (API ou banco) --}}
    @isset($pokemon)

    {{-- Prepara variaveis de cor e traducao de stats para o card --}}
    @php
        $typeColors = [
            'normal'   => ['bg' => '#A8A878', 'light' => '#C8C8A8', 'text' => 'white'],
            'fire'     => ['bg' => '#E8542A', 'light' => '#F08030', 'text' => 'white'],
            'water'    => ['bg' => '#4070C8', 'light' => '#6890F0', 'text' => 'white'],
            'electric' => ['bg' => '#D8B020', 'light' => '#F8D030', 'text' => '#333'],
            'grass'    => ['bg' => '#4E9A3A', 'light' => '#78C850', 'text' => 'white'],
            'ice'      => ['bg' => '#68B8B8', 'light' => '#98D8D8', 'text' => '#333'],
            'fighting' => ['bg' => '#A82820', 'light' => '#C03028', 'text' => 'white'],
            'poison'   => ['bg' => '#803880', 'light' => '#A040A0', 'text' => 'white'],
            'ground'   => ['bg' => '#C8A038', 'light' => '#E0C068', 'text' => '#333'],
            'flying'   => ['bg' => '#8870D8', 'light' => '#A890F0', 'text' => 'white'],
            'psychic'  => ['bg' => '#D83060', 'light' => '#F85888', 'text' => 'white'],
            'bug'      => ['bg' => '#809818', 'light' => '#A8B820', 'text' => 'white'],
            'rock'     => ['bg' => '#908028', 'light' => '#B8A038', 'text' => 'white'],
            'ghost'    => ['bg' => '#584878', 'light' => '#705898', 'text' => 'white'],
            'dragon'   => ['bg' => '#5018C8', 'light' => '#7038F8', 'text' => 'white'],
            'dark'     => ['bg' => '#504038', 'light' => '#705848', 'text' => 'white'],
            'steel'    => ['bg' => '#909090', 'light' => '#B8B8D0', 'text' => '#333'],
            'fairy'    => ['bg' => '#C070A0', 'light' => '#EE99AC', 'text' => '#333'],
        ];
        $statLabels = [
            'hp'              => 'HP',
            'attack'          => 'Ataque',
            'defense'         => 'Defesa',
            'special-attack'  => 'Atq. Esp.',
            'special-defense' => 'Def. Esp.',
            'speed'           => 'Velocidade',
        ];
        $statColors = [
            'hp'              => '#FF5959',
            'attack'          => '#F5AC78',
            'defense'         => '#FAE078',
            'special-attack'  => '#9DB7F5',
            'special-defense' => '#A7DB8D',
            'speed'           => '#FA92B2',
        ];
        $primaryType = $pokemon['types'][0] ?? 'normal';
        $typeColor   = $typeColors[$primaryType] ?? $typeColors['normal'];
        $headerBg    = $typeColor['bg'];
        $headerLight = $typeColor['light'];
    @endphp

    {{-- card-enter: aplica a animação de entrada definida no CSS acima --}}
    <div class="max-w-md mx-auto mt-8 card-enter">
        {{-- Borda colorida do card com a cor do tipo primário do Pokémon --}}
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden" style="border: 4px solid {{ $headerLight }};">

            {{-- CABEÇALHO DO CARD — fundo gradiente na cor do tipo --}}
            <div class="relative px-6 pt-6 pb-0 text-center"
                 style="background: linear-gradient(180deg, {{ $headerBg }}22 0%, {{ $headerBg }}55 100%);">

                {{-- Elemento decorativo: Pokébola semitransparente no canto --}}
                <div class="absolute top-2 right-4 text-5xl opacity-10 select-none pointer-events-none">⚪</div>

                {{-- Número da Pokédex formatado com 4 dígitos: ex: #0025
                     str_pad() → preenche com zeros à esquerda até ter 4 caracteres
                     $pokemon['id'] → vem do PokemonController (format ou formatLocal) --}}
                <span class="text-xs font-bold tracking-widest" style="color: {{ $headerBg }};">
                    #{{ str_pad($pokemon['id'], 4, '0', STR_PAD_LEFT) }}
                </span>

                {{-- Nome do Pokémon
                     capitalize → primeira letra maiúscula via CSS
                     $pokemon['name'] → vem em minúsculo da API e do banco --}}
                <h2 class="text-3xl font-bold text-gray-800 capitalize mt-1 mb-3">
                    {{ $pokemon['name'] }}
                </h2>

                {{-- BADGES DE TIPO — itera sobre o array de tipos do Pokémon
                     $pokemon['types'] = ['water', 'rock'] → exibe 2 badges
                     $pokemon['types'] = ['fire']          → exibe 1 badge
                     A cor de cada badge vem do array $typeColors definido acima --}}
                <div class="flex justify-center gap-2 mb-4">
                    @foreach($pokemon['types'] as $type)
                        {{-- Busca as cores do tipo atual no mapa $typeColors
                             ?? $typeColors['normal'] → fallback para tipos desconhecidos --}}
                        @php $tc = $typeColors[$type] ?? $typeColors['normal']; @endphp
                        <span class="px-5 py-1 rounded-full text-sm font-bold capitalize shadow-sm"
                              style="background-color: {{ $tc['bg'] }}; color: {{ $tc['text'] }};">
                            {{ $type }}
                        </span>
                    @endforeach
                </div>

                {{-- IMAGEM DO POKÉMON
                     $pokemon['image'] pode ser:
                     - URL do artwork oficial da PokeAPI (para Pokémons da API)
                     - URL gerada por Storage::url() (para Pokémons do banco local)
                     Se não houver imagem, exibe um ❓ como fallback --}}
                <div class="flex justify-center pb-2"
                     style="background: radial-gradient(ellipse 80% 60% at 50% 80%, {{ $headerBg }}44 0%, transparent 70%);">
                    @if($pokemon['image'])
                        <img src="{{ $pokemon['image'] }}"
                             alt="{{ $pokemon['name'] }}"
                             class="w-44 h-44 object-contain drop-shadow-xl">
                    @else
                        <div class="w-44 h-44 flex items-center justify-center text-7xl">❓</div>
                    @endif
                </div>
            </div>

            {{-- CORPO DO CARD --}}
            <div class="px-6 pb-6 pt-5">

                {{-- ALTURA E PESO
                     $pokemon['height'] e ['weight'] chegam em decímetros e hectogramas
                     (tanto da PokeAPI quanto formatados por formatLocal())
                     Divisão por 10 converte para metros e kg respectivamente
                     number_format(..., 1) → exibe com 1 casa decimal: ex: 1.3 m --}}
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="bg-gray-50 rounded-2xl p-3 text-center">
                        <p class="text-gray-400 text-xs mb-1">Altura</p>
                        <p class="text-gray-800 font-bold text-lg">{{ number_format($pokemon['height'] / 10, 1) }} m</p>
                    </div>
                    <div class="bg-gray-50 rounded-2xl p-3 text-center">
                        <p class="text-gray-400 text-xs mb-1">Peso</p>
                        <p class="text-gray-800 font-bold text-lg">{{ number_format($pokemon['weight'] / 10, 1) }} kg</p>
                    </div>
                </div>

                {{-- HABILIDADES
                     $pokemon['abilities'] é um array de strings: ['blaze', 'solar-power']
                     str_replace('-', ' ', $ability) → substitui hífens por espaços
                     Ex: 'solar-power' → 'solar power' --}}
                <div class="mb-5">
                    <h3 class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-2">Habilidades</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($pokemon['abilities'] as $ability)
                            <span class="bg-gray-100 text-gray-600 px-4 py-1.5 rounded-xl text-sm font-medium capitalize">
                                {{ str_replace('-', ' ', $ability) }}
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- BARRAS DE ESTATÍSTICAS
                     $pokemon['stats'] é array de arrays: [['name'=>'hp','value'=>80], ...]
                     Para cada stat:
                     1. Busca o label em português no array $statLabels
                     2. Busca a cor da barra no array $statColors
                     3. Calcula a porcentagem (valor / 255 * 100) para a largura da barra
                        255 é o valor máximo possível de uma stat na franquia Pokémon --}}
                <div>
                    <h3 class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-3">Estatísticas Base</h3>
                    @foreach($pokemon['stats'] as $stat)
                        @php
                            // Traduz o nome da stat ou usa o nome original se não encontrar
                            $label = $statLabels[$stat['name']] ?? $stat['name'];

                            // Cor da barra — fallback para cinza se o tipo não tiver mapeamento
                            $color = $statColors[$stat['name']] ?? '#A8A878';

                            // Calcula porcentagem limitada a 100%
                            // Ex: hp=80 → (80/255)*100 = 31.4% de largura
                            $pct   = round(min(($stat['value'] / 255) * 100, 100));
                        @endphp
                        <div class="mb-2.5">
                            <div class="flex items-center gap-3 mb-1">
                                {{-- Label da stat em português --}}
                                <span class="text-gray-500 text-xs w-20 shrink-0">{{ $label }}</span>
                                {{-- Valor numérico da stat --}}
                                <span class="text-gray-800 font-bold text-xs w-7 text-right shrink-0">{{ $stat['value'] }}</span>
                                {{-- Trilho cinza da barra --}}
                                <div class="flex-1 bg-gray-100 rounded-full h-2">
                                    {{-- stat-bar: classe CSS que aplica a transição de largura
                                         width calculado em % e cor definidos inline --}}
                                    <div class="stat-bar h-2 rounded-full"
                                         style="width: {{ $pct }}%; background-color: {{ $color }};"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

        </div>
    </div>

    @endisset {{-- Fecha o @isset($pokemon) --}}

    {{-- ===== SEÇÃO 4: ESTADO VAZIO ===== --}}
    {{-- Exibido apenas quando:
         1. Não há $pokemon (nenhuma busca foi feita ainda)
         2. Não há mensagem de erro na sessão
         Ou seja: é a tela inicial antes de qualquer interação --}}
    @if (!isset($pokemon) && !session('error'))
        <div class="text-center mt-12 select-none">
            <div class="text-8xl mb-5 opacity-70">⚡</div>
            <p class="text-white text-xl font-semibold">Comece sua aventura!</p>
            <p class="text-red-200 text-sm mt-2">Digite o nome de um Pokémon ou clique em Aleatório</p>
        </div>
    @endif

    {{-- ===== SEÇÃO 5: MENSAGEM DE SUCESSO ===== --}}
    {{-- session('success') → gravada pelo MeuPokemonController via with('success', '...')
         Exibida após criar ou remover um Pokémon com sucesso
         O Laravel limpa a sessão automaticamente após exibir --}}
    @if (session('success'))
        <div class="max-w-md mx-auto mt-6 flex items-center gap-3 bg-green-600/90 text-white px-5 py-4 rounded-2xl border border-green-500 text-sm shadow">
            <span class="text-lg">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- ===== SEÇÃO 6: GRID DE MEUS POKÉMONS ===== --}}
    {{-- $meusPokemons → coleção enviada por PokemonController
         Todos os registros da tabela meus_pokemons, ordenados do mais recente
         O "|| true" garante que a seção sempre apareça (mesmo sem Pokémons) --}}
    @if(isset($meusPokemons) && $meusPokemons->count() > 0 || true)
    <div class="max-w-2xl mx-auto mt-14">

        {{-- Cabeçalho da seção com contador e botão de criação --}}
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-white font-bold text-lg">Meus Pokémons
                {{-- Exibe a quantidade entre parênteses se houver registros --}}
                @if(isset($meusPokemons) && $meusPokemons->count() > 0)
                    <span class="text-yellow-300 text-sm font-normal ml-2">({{ $meusPokemons->count() }})</span>
                @endif
            </h2>
            {{-- Botão "+ Criar Pokémon"
                 route('meu-pokemon.create') → GET /meu-pokemon/criar
                 Definido em routes/web.php → MeuPokemonController@create
                 Leva para resources/views/pokemon/create.blade.php --}}
            <a href="{{ route('meu-pokemon.create') }}"
               class="bg-yellow-300 hover:bg-yellow-200 text-gray-800 font-bold px-4 py-2 rounded-xl text-sm shadow transition-all border-b-2 border-yellow-500 active:border-b-0 active:translate-y-px">
                + Criar Pokémon
            </a>
        </div>

        @if(isset($meusPokemons) && $meusPokemons->count() > 0)

        {{-- Mapa de cores simplificado para os cards do grid
             (apenas a cor principal, sem 'light' e 'text' como no card grande) --}}
        @php
            $typeColors = [
                'normal'=>'#A8A878','fire'=>'#E8542A','water'=>'#4070C8',
                'electric'=>'#D8B020','grass'=>'#4E9A3A','ice'=>'#68B8B8',
                'fighting'=>'#A82820','poison'=>'#803880','ground'=>'#C8A038',
                'flying'=>'#8870D8','psychic'=>'#D83060','bug'=>'#809818',
                'rock'=>'#908028','ghost'=>'#584878','dragon'=>'#5018C8',
                'dark'=>'#504038','steel'=>'#909090','fairy'=>'#C070A0',
            ];
        @endphp

        {{-- Grid responsivo: 1 coluna em mobile, 2 colunas em telas maiores --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            {{-- Itera sobre cada Pokémon do banco ($meusPokemons = coleção Eloquent)
                 $mp → instância de app/Models/MeuPokemon.php --}}
            @foreach($meusPokemons as $mp)
            @php
                // tiposArray() → método do Model que divide "water,rock" em ["water","rock"]
                // Conecta com: app/Models/MeuPokemon.php
                $tipos  = $mp->tiposArray();

                // Pega a cor do tipo primário para estilizar o card
                // ?? 'normal' → fallback caso o array esteja vazio
                $corPri = $typeColors[$tipos[0] ?? 'normal'] ?? '#A8A878';
            @endphp

            {{-- Card individual do Pokémon do banco
                 card-enter → animação de entrada definida no CSS --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex card-enter" style="border: 2px solid {{ $corPri }}22;">

                {{-- ÁREA DA IMAGEM — fundo com gradiente na cor do tipo --}}
                <div class="w-28 shrink-0 flex items-center justify-center p-3"
                     style="background: linear-gradient(135deg, {{ $corPri }}22, {{ $corPri }}44);">
                    @if($mp->imagem)
                        {{-- Storage::url() converte o caminho relativo em URL pública
                             Ex: 'pokemon-images/Aethelfin.png'
                             → '/storage/pokemon-images/Aethelfin.png'
                             Funciona graças ao symlink: public/storage → storage/app/public --}}
                        <img src="{{ Storage::url($mp->imagem) }}"
                             alt="{{ $mp->nome }}"
                             class="w-20 h-20 object-contain drop-shadow-md">
                    @else
                        {{-- Fallback visual quando não há imagem cadastrada --}}
                        <div class="w-20 h-20 flex items-center justify-center text-4xl opacity-40">❓</div>
                    @endif
                </div>

                {{-- ÁREA DE INFORMAÇÕES --}}
                <div class="flex-1 p-4 min-w-0">
                    {{-- Número do registro no banco (ID da tabela meus_pokemons) --}}
                    <p class="text-xs text-gray-400 font-bold">#{{ str_pad($mp->id, 4, '0', STR_PAD_LEFT) }}</p>

                    {{-- Nome do Pokémon — $mp->nome acessa a coluna 'nome' da tabela --}}
                    <h3 class="font-bold text-gray-800 text-base capitalize truncate">{{ $mp->nome }}</h3>

                    {{-- Badges de tipo — usa $tipos que já veio de tiposArray() --}}
                    <div class="flex flex-wrap gap-1 mt-1.5">
                        @foreach($tipos as $tipo)
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold capitalize text-white"
                                  style="background-color: {{ $typeColors[$tipo] ?? '#A8A878' }};">
                                {{ $tipo }}
                            </span>
                        @endforeach
                    </div>

                    {{-- Stats em formato compacto (grid 3x2)
                         $mp->stats() → método do Model que retorna as 6 stats formatadas
                         Conecta com: app/Models/MeuPokemon.php --}}
                    <div class="grid grid-cols-3 gap-x-3 gap-y-0.5 mt-2.5">
                        @foreach($mp->stats() as $s)
                            <div class="text-xs text-gray-500">
                                <span class="font-semibold text-gray-700">{{ $s['value'] }}</span>
                                <span class="ml-0.5">{{ $s['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- BOTÃO DE REMOVER
                     HTML não suporta DELETE nativamente — só GET e POST
                     @method('DELETE') → adiciona um campo hidden _method=DELETE
                     O Laravel lê esse campo e trata como DELETE
                     onsubmit confirm() → caixa de confirmação JavaScript --}}
                <div class="flex items-start p-3">
                    <form action="{{ route('meu-pokemon.destroy', $mp->id) }}" method="POST"
                          onsubmit="return confirm('Remover {{ $mp->nome }}?')">
                        {{-- @csrf obrigatório em todo formulário POST/DELETE --}}
                        @csrf
                        {{-- @method('DELETE') → _method=DELETE para simular o verbo HTTP DELETE --}}
                        @method('DELETE')
                        <button type="submit"
                                class="text-gray-300 hover:text-red-400 transition-colors text-lg leading-none"
                                title="Remover">
                            ✕
                        </button>
                    </form>
                </div>

            </div>
            @endforeach
        </div>

        @else
        {{-- Estado vazio: exibido quando $meusPokemons está vazia (sem registros no banco) --}}
        <div class="bg-white/10 rounded-2xl p-8 text-center text-red-200 border border-white/20">
            <div class="text-4xl mb-3">📭</div>
            <p class="font-semibold">Nenhum Pokémon cadastrado ainda.</p>
            <p class="text-sm mt-1 opacity-75">Clique em "Criar Pokémon" para começar!</p>
        </div>
        @endif

    </div>
    @endif

</div>

{{-- ===== JAVASCRIPT ===== --}}
<script>
    /*
    | Feedback visual de loading no botão "Pokémon Aleatório"
    | Quando o usuário clica, troca o ícone e texto para indicar que está carregando
    | e desabilita o botão para evitar cliques duplicados durante a requisição
    */
    document.getElementById('random-btn').addEventListener('click', function () {
        document.getElementById('random-icon').textContent = '⏳';
        document.getElementById('random-text').textContent = 'Carregando...';
        // pointer-events-none → desabilita cliques adicionais
        // opacity-75 → escurece levemente para indicar estado desativado
        this.classList.add('pointer-events-none', 'opacity-75');
    });

    /*
    | Feedback visual de loading no botão de busca
    | Troca o ícone 🔍 por ⏳ e desabilita o botão
    */
    document.getElementById('search-form').addEventListener('submit', function () {
        const btn = document.getElementById('search-btn');
        btn.textContent = '⏳';
        btn.disabled = true; // disabled → impede múltiplos envios do formulário
    });

    /*
    | Foco automático no campo de busca ao carregar a página
    | Melhora a usabilidade — usuário já pode digitar sem precisar clicar
    | O 'if (input)' evita erro caso o elemento não exista na DOM
    */
    const input = document.getElementById('search-input');
    if (input) input.focus();
</script>

</body>
</html>
