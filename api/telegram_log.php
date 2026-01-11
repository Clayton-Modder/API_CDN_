<?php

$token  = "8553413416:AAETpmhLL933bqSv__RsVtp9j8RJI1D3WjI";
$chatId = "-5286992033";

/* =========================
   FUNÇÃO PARA PEGAR IP REAL
========================= */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP']; // Cloudflare
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'IP não identificado';
}

$ip = getUserIP();

/* =========================
   DADOS DO FORMULÁRIO
========================= */
$canal = $_POST['canal'] ?? 'N/A';
$url   = $_POST['url'] ?? 'N/A';
$erro  = $_POST['erro'] ?? 'N/A';

/* =========================
   MENSAGEM
========================= */
$caption  = "🚨 Relatório de Canal\n\n";
$caption .= "Canal: $canal\n";
$caption .= "Problema: $erro\n";
$caption .= "URL: $url\n";
$caption .= "IP do usuário: $ip\n";
$caption .= "Data: " . date("d/m/Y H:i:s") . "\n";

/* =========================
   ENVIO COM OU SEM ANEXO
========================= */

if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {

    $tmp  = $_FILES['anexo']['tmp_name'];
    $name = $_FILES['anexo']['name'];
    $type = $_FILES['anexo']['type'];

    // Decide endpoint
    if (strpos($type, 'image/') === 0) {
        $endpoint = "sendPhoto";
        $field = "photo";
    } else {
        $endpoint = "sendDocument";
        $field = "document";
    }

    $ch = curl_init("https://api.telegram.org/bot$token/$endpoint");

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'chat_id' => $chatId,
            'caption' => $caption,
            $field => new CURLFile($tmp, $type, $name)
        ]
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        echo "ERRO_CURL: $error";
        exit;
    }

} else {

    // SEM ANEXO
    $ch = curl_init("https://api.telegram.org/bot$token/sendMessage");

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'chat_id' => $chatId,
            'text' => $caption
        ]
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        echo "ERRO_CURL: $error";
        exit;
    }
}

echo "OK";
