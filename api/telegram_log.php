<?php
$BOT_TOKEN = "8553413416:AAETpmhLL933bqSv__RsVtp9j8RJI1D3WjI";
$CHAT_ID  = "-3621587880";

$canal = $_POST['canal'] ?? 'Desconhecido';
$url   = $_POST['url'] ?? '';
$erro  = $_POST['erro'] ?? '';

$msg = "🚨 *Relatório de Canal*\n\n"
     . "📺 Canal: `$canal`\n"
     . "❌ Problema: $erro\n"
     . "🔗 URL: $url";

file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chatId&text=" . urlencode($msg) . "&parse_mode=Markdown");
