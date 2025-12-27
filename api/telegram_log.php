<?php
$BOT_TOKEN = "7542514291:AAH9eZgl511x2slhR14G0sxoxRZ4Eo-Ho-M";
$CHAT_ID  = "-5237706159";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

$canal = $_POST['canal'] ?? 'desconhecido';
$url   = $_POST['url'] ?? 'sem_url';
$erro  = $_POST['erro'] ?? 'erro_nao_informado';
$data  = date('d/m/Y H:i:s');

$mensagem = "🚨 *Falha no Player*\n\n".
            "📺 Canal: `$canal`\n".
            "🌐 URL: `$url`\n".
            "❌ Motivo: `$erro`\n".
            "🕒 Data: `$data`";

$telegramUrl = "https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage";

$post = [
    'chat_id' => $CHAT_ID,
    'text' => $mensagem,
    'parse_mode' => 'Markdown'
];

$ch = curl_init($telegramUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);
