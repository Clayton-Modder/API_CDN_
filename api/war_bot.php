<?php
/**
 * ╔══════════════════════════════════════════════════════╗
 * ║         BOT TELEGRAM - SISTEMA DE GUERRAS           ║
 * ║              Entre as Grandes Máfias                ║
 * ╚══════════════════════════════════════════════════════╝
 *
 * CONFIGURAÇÃO: Coloque seu token do bot abaixo.
 * Rode com: php war_bot.php (webhook) ou polling abaixo.
 */

define('BOT_TOKEN', 'SEU_TOKEN_AQUI'); // 🔑 Substitua pelo token do @BotFather
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');
define('DB_FILE', __DIR__ . '/war_data.json'); // Banco de dados simples em JSON
define('WAR_COOLDOWN', 20 * 60); // 20 minutos em segundos

// ════════════════════════════════════════════════════════
//  MÁFIAS REGISTRADAS (com e sem loja)
// ════════════════════════════════════════════════════════
$MAFIAS = [
    'os_cara_de_verdade' => [
        'nome'   => '🃏 Os Cara de Verdade',
        'lider'  => 'Matheus Bar',
        'loja'   => false,
    ],
    'peaky_blinders' => [
        'nome'   => '🎩 Peaky Blinders',
        'lider'  => 'Gui_Vilao',
        'loja'   => true,   // ⭐ Tem loja - MAIS CHANCE NO SORTEIO
    ],
    'tambov_criminal' => [
        'nome'   => '🦅 Tambov Criminal',
        'lider'  => 'Mr_Capone',
        'loja'   => true,   // ⭐ Tem loja - MAIS CHANCE NO SORTEIO
    ],
    'ismael_criminal' => [
        'nome'   => '🐍 Ismael Criminal',
        'lider'  => 'Maya Dias',
        'loja'   => false,
    ],
];

// ════════════════════════════════════════════════════════
//  FUNÇÕES DE BANCO DE DADOS (JSON simples)
// ════════════════════════════════════════════════════════

function loadDB(): array {
    if (!file_exists(DB_FILE)) {
        return ['wars' => [], 'results' => []];
    }
    return json_decode(file_get_contents(DB_FILE), true) ?? ['wars' => [], 'results' => []];
}

function saveDB(array $data): void {
    file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ════════════════════════════════════════════════════════
//  FUNÇÕES DE API TELEGRAM
// ════════════════════════════════════════════════════════

function apiRequest(string $method, array $params = []): ?array {
    $url = API_URL . $method;
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($params),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

function sendMessage(int|string $chatId, string $text, array $extra = []): void {
    apiRequest('sendMessage', array_merge([
        'chat_id'    => $chatId,
        'text'       => $text,
        'parse_mode' => 'HTML',
    ], $extra));
}

function sendPhoto(int|string $chatId, string $caption): void {
    // Envia mensagem formatada como "banner" usando apenas texto
    sendMessage($chatId, $caption);
}

// ════════════════════════════════════════════════════════
//  LÓGICA DE SORTEIO
//  Máfias com LOJA têm PESO DUPLO no sorteio
// ════════════════════════════════════════════════════════

function sortearMafias(): array {
    global $MAFIAS;

    // Monta pool ponderado: máfias com loja entram 2x
    $pool = [];
    foreach ($MAFIAS as $id => $m) {
        $pool[] = $id;
        if ($m['loja']) {
            $pool[] = $id; // peso extra
        }
    }

    // Sorteia atacante
    shuffle($pool);
    $atacante = $pool[array_rand($pool)];

    // Sorteia defensor diferente
    do {
        shuffle($pool);
        $defensor = $pool[array_rand($pool)];
    } while ($defensor === $atacante);

    return [$atacante, $defensor];
}

// ════════════════════════════════════════════════════════
//  LÓGICA DA GUERRA
// ════════════════════════════════════════════════════════

function iniciarGuerraSorteio(int $chatId): void {
    global $MAFIAS;

    $db = loadDB();

    // ── Verificar cooldown global (qualquer guerra nos últimos 20min)
    $lastWar = $db['wars']['last_war_time'] ?? 0;
    $agora   = time();
    $elapsed = $agora - $lastWar;

    if ($elapsed < WAR_COOLDOWN) {
        $restante = WAR_COOLDOWN - $elapsed;
        $min      = floor($restante / 60);
        $seg      = $restante % 60;
        sendMessage($chatId,
            "⏳ <b>AGUARDE!</b>\n\n" .
            "Uma guerra já foi iniciada recentemente.\n" .
            "Próxima guerra disponível em: <b>{$min}m {$seg}s</b>\n\n" .
            "Restrição de <b>20 minutos</b> entre guerras."
        );
        return;
    }

    // ── Sortear as máfias
    [$atkId, $defId] = sortearMafias();
    $atk = $MAFIAS[$atkId];
    $def = $MAFIAS[$defId];

    // ── Simular resultado da batalha (50/50 base, loja dá +10%)
    $chanceAtk = 50 + ($atk['loja'] ? 10 : 0) - ($def['loja'] ? 10 : 0);
    $chanceAtk = max(20, min(80, $chanceAtk)); // entre 20% e 80%
    $atkVenceu = (rand(1, 100) <= $chanceAtk);

    $vencedor = $atkVenceu ? $atk : $def;
    $perdedor = $atkVenceu ? $def : $atk;
    $vencId   = $atkVenceu ? $atkId : $defId;
    $perdId   = $atkVenceu ? $defId : $atkId;

    // ── Atualizar banco de dados
    $db['wars']['last_war_time'] = $agora;

    // Contagem de vitórias/derrotas
    foreach ([$atkId, $defId] as $mid) {
        if (!isset($db['results'][$mid])) {
            $db['results'][$mid] = ['vitorias' => 0, 'derrotas' => 0];
        }
    }
    $db['results'][$vencId]['vitorias']++;
    $db['results'][$perdId]['derrotas']++;

    // Histórico
    $db['wars']['historico'][] = [
        'data'     => date('d/m/Y H:i'),
        'atacante' => $atkId,
        'defensor' => $defId,
        'vencedor' => $vencId,
    ];

    saveDB($db);

    // ── Montar mensagem de anúncio
    $lojaAtkEmoji  = $atk['loja'] ? ' 🏪' : '';
    $lojaDefEmoji  = $def['loja'] ? ' 🏪' : '';
    $resultEmoji   = $atkVenceu ? '🏆' : '🛡️';

    $msg  = "╔══════════════════════════╗\n";
    $msg .= "║     ⚔️  <b>GUERRA INICIADA</b>  ⚔️     ║\n";
    $msg .= "╚══════════════════════════╝\n\n";

    $msg .= "🗡️ <b>ATACANTE</b>\n";
    $msg .= "  {$atk['nome']}{$lojaAtkEmoji}\n";
    $msg .= "  👑 Líder: <b>{$atk['lider']}</b>\n\n";

    $msg .= "🛡️ <b>DEFENSOR</b>\n";
    $msg .= "  {$def['nome']}{$lojaDefEmoji}\n";
    $msg .= "  👑 Líder: <b>{$def['lider']}</b>\n\n";

    $msg .= "━━━━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "💥 <i>A batalha foi travada...</i>\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $msg .= "{$resultEmoji} <b>VENCEDOR:</b> {$vencedor['nome']}\n";
    $msg .= "   Líder: <b>{$vencedor['lider']}</b>\n\n";

    $msg .= "💀 <b>DERROTADO:</b> {$perdedor['nome']}\n";
    $msg .= "   Líder: <b>{$perdedor['lider']}</b>\n\n";

    $msg .= "⏱️ Próxima guerra em <b>20 minutos</b>\n";
    $msg .= "🏪 = Organização com Loja (sorteio bônus)";

    sendMessage($chatId, $msg);
}

function mostrarRanking(int $chatId): void {
    global $MAFIAS;

    $db      = loadDB();
    $results = $db['results'] ?? [];

    // Montar ranking
    $ranking = [];
    foreach ($MAFIAS as $id => $m) {
        $v = $results[$id]['vitorias'] ?? 0;
        $d = $results[$id]['derrotas'] ?? 0;
        $ranking[] = ['id' => $id, 'mafia' => $m, 'v' => $v, 'd' => $d];
    }
    usort($ranking, fn($a, $b) => $b['v'] <=> $a['v']);

    $msg  = "🏆 <b>RANKING DAS MÁFIAS</b> 🏆\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $medals = ['🥇', '🥈', '🥉', '4️⃣'];
    foreach ($ranking as $i => $r) {
        $loja = $r['mafia']['loja'] ? ' 🏪' : '';
        $msg .= "{$medals[$i]} <b>{$r['mafia']['nome']}</b>{$loja}\n";
        $msg .= "   👑 {$r['mafia']['lider']}\n";
        $msg .= "   ✅ Vitórias: <b>{$r['v']}</b>  ❌ Derrotas: <b>{$r['d']}</b>\n\n";
    }

    $msg .= "🏪 = Tem loja (bônus no sorteio)";
    sendMessage($chatId, $msg);
}

function mostrarMafias(int $chatId): void {
    global $MAFIAS;

    $msg  = "🔱 <b>ORGANIZAÇÕES REGISTRADAS</b> 🔱\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    foreach ($MAFIAS as $m) {
        $loja = $m['loja'] ? "\n   🏪 <i>Possui loja (bônus no sorteio)</i>" : '';
        $msg .= "{$m['nome']}\n";
        $msg .= "   👑 Líder: <b>{$m['lider']}</b>{$loja}\n\n";
    }

    $msg .= "━━━━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "Use /gstart para iniciar uma guerra!\n";
    $msg .= "⏱️ Cooldown entre guerras: <b>20 minutos</b>";
    sendMessage($chatId, $msg);
}

function mostrarAjuda(int $chatId): void {
    $msg  = "📖 <b>COMANDOS DO BOT DE GUERRAS</b>\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $msg .= "/gstart — 🎲 Sortear e iniciar uma guerra\n";
    $msg .= "/mafias — 🔱 Ver todas as organizações\n";
    $msg .= "/ranking — 🏆 Ver ranking de vitórias\n";
    $msg .= "/ajuda   — 📖 Mostrar este menu\n\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "⚔️ O sorteio escolhe aleatoriamente qual máfia\n";
    $msg .= "ataca qual. <b>Máfias com loja</b> têm maior chance\n";
    $msg .= "de serem sorteadas!\n\n";
    $msg .= "⏱️ Restrição: <b>20 minutos</b> entre guerras.";
    sendMessage($chatId, $msg);
}

// ════════════════════════════════════════════════════════
//  PROCESSAMENTO DE UPDATES (Webhook ou Polling)
// ════════════════════════════════════════════════════════

function processUpdate(array $update): void {
    $message = $update['message'] ?? $update['channel_post'] ?? null;
    if (!$message) return;

    $chatId = $message['chat']['id'];
    $text   = trim($message['text'] ?? '');

    // Remove @NomeDoBot do comando se houver
    $text = preg_replace('/@\w+/', '', $text);
    $text = trim($text);

    switch (true) {
        case str_starts_with($text, '/gstart'):
            iniciarGuerraSorteio($chatId);
            break;

        case str_starts_with($text, '/mafias'):
            mostrarMafias($chatId);
            break;

        case str_starts_with($text, '/ranking'):
            mostrarRanking($chatId);
            break;

        case str_starts_with($text, '/ajuda'):
        case str_starts_with($text, '/start'):
        case str_starts_with($text, '/help'):
            mostrarAjuda($chatId);
            break;
    }
}

// ════════════════════════════════════════════════════════
//  MODO DE EXECUÇÃO
//  → Se receber via Webhook (servidor web): lê o JSON do body
//  → Se rodar via CLI: usa Long Polling
// ════════════════════════════════════════════════════════

if (PHP_SAPI === 'cli') {
    // ── MODO POLLING (para testes locais)
    echo "🤖 Bot iniciado em modo polling...\n";
    $offset = 0;
    while (true) {
        $res = apiRequest('getUpdates', ['offset' => $offset, 'timeout' => 30]);
        foreach ($res['result'] ?? [] as $update) {
            processUpdate($update);
            $offset = $update['update_id'] + 1;
        }
    }
} else {
    // ── MODO WEBHOOK (em servidor web)
    $input  = file_get_contents('php://input');
    $update = json_decode($input, true);
    if ($update) {
        processUpdate($update);
    }
    http_response_code(200);
}
