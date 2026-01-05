
<?php
$token  = "8553413416:AAETpmhLL933bqSv__RsVtp9j8RJI1D3WjI";
$chatId = "-5286992033";

$canal = $_POST['canal'] ?? 'N/A';
$url   = $_POST['url'] ?? 'N/A';
$erro  = $_POST['erro'] ?? 'N/A';

$msg  = "🚨 Relatório de Canal\n\n";
$msg .= "Canal: $canal\n";
$msg .= "Problema: $erro\n";
$msg .= "URL: $url";

$ch = curl_init("https://api.telegram.org/bot$token/sendMessage");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => [
        'chat_id' => $chatId,
        'text' => $msg
    ]
]);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($response === false) {
    echo "ERRO CURL: $err";
} else {
    echo "OK";
}
