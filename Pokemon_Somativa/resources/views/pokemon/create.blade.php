{{--
|--------------------------------------------------------------------------
| ARQUIVO: resources/views/pokemon/create.blade.php
|--------------------------------------------------------------------------
| Esta é a VIEW do formulário de criação de Pokémon.
|
| Caminho na pasta: resources/views/pokemon/create.blade.php
| Chamada pelo Controller como: view('pokemon.create')
| (ponto equivale a barra de pasta no Laravel Blade)
|
| DADOS ENVIADOS PELO FORMULÁRIO → MeuPokemonController@store:
|  nome, imagem (arquivo), tipo1, tipo2, altura, peso,
|  hp, ataque, defesa, ataque_especial, defesa_especial, velocidade, habilidades
|
| CONEXÕES DESTE ARQUIVO:
|  ← app/Http/Controllers/MeuPokemonController@create (renderiza esta view)
|  → routes/web.php via route()        (o formulário aponta para meu-pokemon.store)
|  → app/Http/Controllers/MeuPokemonController@store  (recebe o POST)
|  → JavaScript nativo                 (preview de imagem + barras de stat animadas)
|--------------------------------------------------------------------------
--}}
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Pokémon — PokéDex</title>

    {{-- Tailwind CSS via CDN — mesma configuração da welcome.blade.php --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Google Fonts: Press Start 2P (título) e Inter (corpo) --}}
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        {{-- Adiciona as fontes customizadas ao Tailwind --}}
        tailwind.config = {
            theme: { extend: { fontFamily: { pixel: ['"Press Start 2P"', 'cursive'], sans: ['Inter','sans-serif'] } } }
        }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; }

        /* Estiliza os botões de incremento/decremento dos inputs numéricos
           para ficarem sempre visíveis (por padrão ficam ocultos no Chrome) */
        .stat-input::-webkit-inner-spin-button { opacity: 1; }

        /* Transição suave na caixa de preview da imagem */
        .preview-box { transition: all 0.3s ease; }
    </style>
</head>

{{-- Mesmo fundo vermelho da página principal para consistência visual --}}
<body class="min-h-screen" style="background: linear-gradient(160deg, #CC0000 0%, #EE1111 40%, #CC0000 100%);">

<div class="min-h-screen px-4 py-10">

    {{-- CABEÇALHO COM BOTÃO VOLTAR
         route('home') → GET / → PokemonController@index
         O link "← Voltar" cancela a criação e retorna à página inicial --}}
    <div class="text-center mb-8">
        <a href="{{ route('home') }}" class="inline-block text-red-200 hover:text-white text-sm mb-4 transition-colors">
            ← Voltar
        </a>
        <h1 class="font-pixel text-yellow-300 text-xl md:text-2xl drop-shadow-lg">Criar Pokémon</h1>
        <p class="text-red-200 mt-2 text-sm">Adicione seu Pokémon ao banco de dados</p>
    </div>

    <div class="max-w-2xl mx-auto">

        {{-- BLOCO DE ERROS DE VALIDAÇÃO
             $errors → variável automática do Laravel preenchida quando validate() falha
             no MeuPokemonController@store
             $errors->any() → true se houver pelo menos um erro
             $errors->all() → retorna array com todas as mensagens de erro --}}
        @if ($errors->any())
            <div class="mb-6 bg-red-900/80 text-red-100 px-5 py-4 rounded-2xl border border-red-700 text-sm">
                <p class="font-bold mb-2">⚠️ Corrija os erros abaixo:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORMULÁRIO PRINCIPAL
             action="{{ route('meu-pokemon.store') }}" → aponta para POST /meu-pokemon
             Definido em routes/web.php → MeuPokemonController@store
             method="POST" → método HTTP para envio de dados
             enctype="multipart/form-data" → OBRIGATÓRIO para upload de arquivos (imagem)
             Sem este enctype, o arquivo de imagem não seria enviado ao servidor --}}
        <form action="{{ route('meu-pokemon.store') }}" method="POST" enctype="multipart/form-data"
              class="bg-white rounded-3xl shadow-2xl overflow-hidden" style="border: 4px solid #F8D030;">

            {{-- @csrf → token de segurança obrigatório em todo formulário POST
                 Previne ataques CSRF. Gera: <input type="hidden" name="_token" value="..."> --}}
            @csrf

            {{-- Topo colorido do card com título --}}
            <div class="bg-yellow-300 px-6 py-4 flex items-center gap-3">
                <span class="text-2xl">⚡</span>
                <h2 class="font-bold text-gray-800 text-lg">Dados do Pokémon</h2>
            </div>

            <div class="p-6 space-y-6">

                {{-- SEÇÃO: NOME + IMAGEM (em grid de 2 colunas no desktop) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- CAMPO: NOME
                         name="nome" → recebido em $request->input('nome') no Controller
                         value="{{ old('nome') }}" → se a validação falhar e o usuário
                         for redirecionado de volta, o campo mantém o valor digitado
                         old() é uma função Helper do Laravel que lê os dados da sessão --}}
                    <div>
                        <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2">
                            Nome *
                        </label>
                        <input type="text" name="nome" value="{{ old('nome') }}"
                               placeholder="Ex: Meu Pokémon"
                               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-yellow-400 outline-none text-gray-700 text-sm transition-colors">
                    </div>

                    {{-- CAMPO: IMAGEM (upload de arquivo)
                         O input real (type="file") fica oculto com class="hidden"
                         O label visível (div com borda tracejada) age como botão de abertura
                         onchange="previewImage(this)" → chama função JS ao selecionar arquivo
                         accept="image/*" → filtro do seletor de arquivos para mostrar só imagens --}}
                    <div>
                        <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2">
                            Imagem
                        </label>
                        <div class="flex items-center gap-3">
                            {{-- O label envolve o input oculto: clicar no label abre o seletor --}}
                            <label class="flex-1 cursor-pointer">
                                {{-- Área visual clicável que simula um botão de upload --}}
                                <div class="w-full px-4 py-3 rounded-xl border-2 border-dashed border-gray-300 hover:border-yellow-400 text-center text-sm text-gray-500 transition-colors" id="img-label">
                                    📷 Escolher imagem
                                </div>
                                {{-- Input real oculto — name="imagem" enviado ao Controller --}}
                                <input type="file" name="imagem" accept="image/*" class="hidden" id="img-input"
                                       onchange="previewImage(this)">
                            </label>
                            {{-- Miniatura de preview — atualizada via JavaScript ao selecionar arquivo --}}
                            <div class="w-16 h-16 rounded-xl border-2 border-gray-200 flex items-center justify-center overflow-hidden preview-box" id="img-preview">
                                <span class="text-2xl">❓</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO: TIPOS DO POKÉMON --}}
                <div class="grid grid-cols-2 gap-4">

                    {{-- Arrays de tipos vem do MeuPokemonController@create --}}

                    {{-- SELECT: Tipo 1 (obrigatório)
                         name="tipo1" → recebido pelo Controller como $data['tipo1'] --}}
                    <div>
                        <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2">
                            Tipo 1 *
                        </label>
                        <select name="tipo1"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-yellow-400 outline-none text-gray-700 text-sm bg-white transition-colors">
                            @foreach($tiposDisponiveis as $tipo)
                                {{-- old('tipo1') == $tipo → marca como 'selected' o valor anterior
                                     se o formulário foi submetido com erro --}}
                                <option value="{{ $tipo }}" {{ old('tipo1') == $tipo ? 'selected' : '' }}>
                                    {{ $tiposLabels[$tipo] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- SELECT: Tipo 2 (opcional)
                         name="tipo2" → recebido pelo Controller como $data['tipo2']
                         Opção "— Nenhum —" com value="" → tipo2 será null/vazio --}}
                    <div>
                        <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2">
                            Tipo 2 <span class="text-gray-400 font-normal normal-case">(opcional)</span>
                        </label>
                        <select name="tipo2"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-yellow-400 outline-none text-gray-700 text-sm bg-white transition-colors">
                            <option value="">— Nenhum —</option>
                            @foreach($tiposDisponiveis as $tipo)
                                <option value="{{ $tipo }}" {{ old('tipo2') == $tipo ? 'selected' : '' }}>
                                    {{ $tiposLabels[$tipo] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- SEÇÃO: ALTURA E PESO
                     type="number" → aceita apenas números
                     step="0.1" → permite casas decimais de 0.1 em 0.1
                     Os valores são armazenados em metros e kg na tabela --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2">
                            Altura (m) *
                        </label>
                        {{-- old('altura', '0.5') → valor padrão 0.5 se não houver input anterior --}}
                        <input type="number" name="altura" value="{{ old('altura', '0.5') }}"
                               step="0.1" min="0" max="99"
                               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-yellow-400 outline-none text-gray-700 text-sm transition-colors">
                    </div>
                    <div>
                        <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2">
                            Peso (kg) *
                        </label>
                        <input type="number" name="peso" value="{{ old('peso', '6.5') }}"
                               step="0.1" min="0" max="9999"
                               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-yellow-400 outline-none text-gray-700 text-sm transition-colors">
                    </div>
                </div>

                {{-- CAMPO: HABILIDADES
                     Campo de texto livre — habilidades separadas por vírgula
                     Armazenado como string no banco: "Solid Rock, Hydration"
                     O Controller salva diretamente sem processar --}}
                <div>
                    <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2">
                        Habilidades <span class="text-gray-400 font-normal normal-case">(separe por vírgula)</span>
                    </label>
                    <input type="text" name="habilidades" value="{{ old('habilidades') }}"
                           placeholder="Ex: Levitar, Força bruta"
                           class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-yellow-400 outline-none text-gray-700 text-sm transition-colors">
                </div>

                {{-- SEÇÃO: ESTATÍSTICAS BASE --}}
                <div>
                    <h3 class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-4">
                        Estatísticas Base *
                    </h3>

                    {{-- Array das 6 stats com metadados para renderização dinâmica
                         'field' → name do input e ID da barra (ex: name="hp")
                         'label' → texto exibido na interface
                         'color' → cor da barra de progresso --}}
                    @php
                        $stats = [
                            ['field' => 'hp',              'label' => 'HP',         'color' => '#FF5959'],
                            ['field' => 'ataque',          'label' => 'Ataque',     'color' => '#F5AC78'],
                            ['field' => 'defesa',          'label' => 'Defesa',     'color' => '#FAE078'],
                            ['field' => 'ataque_especial', 'label' => 'Atq. Esp.',  'color' => '#9DB7F5'],
                            ['field' => 'defesa_especial', 'label' => 'Def. Esp.',  'color' => '#A7DB8D'],
                            ['field' => 'velocidade',      'label' => 'Velocidade', 'color' => '#FA92B2'],
                        ];
                    @endphp

                    {{-- Grid 2 colunas para as 6 stats (3 linhas × 2 colunas) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($stats as $stat)
                            <div class="bg-gray-50 rounded-xl p-3">
                                <label class="flex items-center justify-between mb-2">
                                    <span class="text-gray-600 text-xs font-semibold">{{ $stat['label'] }}</span>

                                    {{-- Input numérico da stat
                                         name="{{ $stat['field'] }}" → ex: name="hp", name="ataque"
                                         value="{{ old($stat['field'], 45) }}" → padrão 45 ou valor anterior
                                         min/max → limites de 1 a 255 (validados também no Controller)
                                         oninput="updateBar(...)" → chama JS a cada tecla digitada
                                         'bar-{{ $stat['field'] }}' → ID da barra correspondente --}}
                                    <input type="number" name="{{ $stat['field'] }}"
                                           value="{{ old($stat['field'], 45) }}"
                                           min="1" max="255"
                                           class="stat-input w-16 px-2 py-1 rounded-lg border-2 border-gray-200 focus:border-yellow-400 outline-none text-gray-700 text-sm text-center transition-colors"
                                           oninput="updateBar('bar-{{ $stat['field'] }}', this.value)">
                                </label>

                                {{-- Barra de progresso visual da stat
                                     Trilho cinza com barra colorida por cima
                                     Largura calculada: (valor / 255) * 100 em %
                                     O ID é gerado dinamicamente: id="bar-hp", id="bar-ataque"
                                     A barra é atualizada em tempo real pela função updateBar() --}}
                                <div class="bg-gray-200 rounded-full h-2">
                                    <div id="bar-{{ $stat['field'] }}" class="h-2 rounded-full transition-all duration-300"
                                         style="width: {{ round((old($stat['field'], 45) / 255) * 100) }}%; background-color: {{ $stat['color'] }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- RODAPÉ DO FORMULÁRIO: Cancelar + Salvar --}}
            <div class="bg-gray-50 border-t border-gray-100 px-6 py-4 flex items-center justify-between gap-3">

                {{-- Cancelar → volta para a home sem salvar nada --}}
                <a href="{{ route('home') }}"
                   class="text-gray-500 hover:text-gray-700 text-sm font-medium transition-colors">
                    Cancelar
                </a>

                {{-- Botão de submit — envia todos os dados do formulário para o Controller --}}
                <button type="submit"
                        class="bg-red-500 hover:bg-red-600 active:bg-red-700 text-white font-bold px-8 py-3 rounded-xl shadow-lg transition-all duration-150 border-b-4 border-red-700 active:border-b-0 active:translate-y-0.5">
                    ✅ Salvar Pokémon
                </button>
            </div>

        </form>
    </div>
</div>

{{-- ===== JAVASCRIPT ===== --}}
<script>
    /*
    | previewImage(input)
    | Exibe uma miniatura da imagem selecionada pelo usuário ANTES do upload.
    | Isso é feito lendo o arquivo localmente com FileReader (sem enviar ao servidor).
    |
    | FLUXO:
    |  1. Usuário seleciona uma imagem no input type="file"
    |  2. onchange="previewImage(this)" chama esta função
    |  3. FileReader lê o arquivo como URL base64 (readAsDataURL)
    |  4. Ao terminar (reader.onload), insere a imagem no #img-preview
    |  5. Atualiza o texto do #img-label com o nome do arquivo
    */
    function previewImage(input) {
        const preview = document.getElementById('img-preview'); // div da miniatura
        const label   = document.getElementById('img-label');   // div do texto "Escolher imagem"

        if (input.files && input.files[0]) { // verifica se há arquivo selecionado
            const reader = new FileReader(); // API nativa do navegador para ler arquivos

            // reader.onload → executado quando o FileReader termina de ler o arquivo
            reader.onload = e => {
                // e.target.result → URL base64 da imagem (ex: "data:image/png;base64,...")
                // Substitui o ❓ pela imagem real
                preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                // Mostra o nome do arquivo selecionado
                label.textContent = input.files[0].name;
            };

            // Inicia a leitura do arquivo como Data URL (base64)
            reader.readAsDataURL(input.files[0]);
        }
    }

    /*
    | updateBar(barId, value)
    | Atualiza a largura da barra de progresso em tempo real
    | conforme o usuário digita o valor da estatística.
    |
    | Chamada por: oninput="updateBar('bar-hp', this.value)"
    | barId  → ID da div da barra (ex: 'bar-hp', 'bar-ataque')
    | value  → valor atual do input numérico (ex: 80)
    */
    function updateBar(barId, value) {
        // Garante que o valor fique entre 0 e 255, depois converte para porcentagem
        const pct = Math.min(Math.max(value, 0), 255) / 255 * 100;
        const bar = document.getElementById(barId);
        // Atualiza a largura CSS da barra — a transição CSS anima suavemente
        if (bar) bar.style.width = pct + '%';
    }
</script>

</body>
</html>
