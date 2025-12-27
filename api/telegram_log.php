<?php
$BOT_TOKEN = "7542514291:AAH9eZgl511x2slhR14G0sxoxRZ4Eo-Ho-M";
$CHAT_ID  = "-5237706159";

$canal = $_POST['canal'] ?? 'Desconhecido';
$url   = $_POST['url'] ?? '';
$erro  = $_POST['erro'] ?? '';

$msg = "🚨 *Relatório de Canal*\n\n"
     . "📺 Canal: `$canal`\n"
     . "❌ Problema: $erro\n"
     . "🔗 URL: $url";

file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chatId&text=" . urlencode($msg) . "&parse_mode=Markdown");
