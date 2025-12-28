<?php
$BOT_TOKEN = "7542514291:AAE9epk1TlkBWmG4jLmKluxV81EIJsAwGuU";
$CHAT_ID  = "-3621587880";

$canal = $_POST['canal'] ?? 'Desconhecido';
$url   = $_POST['url'] ?? '';
$erro  = $_POST['erro'] ?? '';

$msg = "🚨 *Relatório de Canal*\n\n"
     . "📺 Canal: `$canal`\n"
     . "❌ Problema: $erro\n"
     . "🔗 URL: $url";

file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chatId&text=" . urlencode($msg) . "&parse_mode=Markdown");
