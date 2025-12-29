<?php
// ================== CONFIGURAÇÃO ==================
$BOT_TOKEN = "8553413416:AAETpmhLL933bqSv__RsVtp9j8RJI1D3WjI";
$CHAT_ID  = "-5286992033"; // ID REAL DO GRUPO (com sinal -)

// ================== HEADERS ==================
header("Content-Type: text/plain; charset=utf-8");

// ================== RECEBE DADOS ==================
$canal = trim($_POST['canal'] ?? '');
$url   = trim($_POST['url'] ?? '');
$erro  = trim($_POST['erro'] ?? '');

// ================== VALIDAÇÃO ==================
if ($erro === '') {
    http_response_code(400);
    exit("Erro não informado");
}

// Limites para evitar spam / erro Telegram
$canal = substr($canal, 0, 60);
$erro  = substr($erro, 0, 300);
$url   = substr($url, 0, 400);

// ================== ESCAPE MARKDOWN V2 ==================
function escapeMarkdown($text) {
    $chars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    foreach ($chars as $c) {
        $text = str_replace($c, '\\' . $c, $text);
    }
    return $text;
}

$canal = escapeMarkdown($canal);
$erro  = escapeMarkdown($erro);
$url   = escapeMarkdown($url);

// ================== MENSAGEM ==================
$msg =
"🚨 *RELATÓRIO DE CANAL*\n\n" .
"📺 *Canal:* `$canal`\n" .
"❌ *Problema:* $erro\n" .
"🔗 *URL:* $url\n\n" .
"🕒 *Data:* " . date("d/m/Y H:i:s");

// ================== ENVIO VIA CURL ==================
$apiUrl = "https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage";

$postData = [
    'chat_id'    => $CHAT_ID,
    'text'       => $msg,
    'parse_mode' => 'MarkdownV2',
    'disable_web_page_preview' => true
];

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ================== RESPOSTA ==================
if ($response === false || $httpCode !== 200) {
    http_response_code(500);
    echo "ERRO AO ENVIAR";
    exit;
}

echo "OK";
