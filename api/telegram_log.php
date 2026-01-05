
<?php
$token  = "8553413416:AAETpmhLL933bqSv__RsVtp9j8RJI1D3WjI";
$chatId = "-5286992033";

$canal = $_POST['canal'] ?? 'N/A';
$url   = $_POST['url'] ?? 'N/A';
$erro  = $_POST['erro'] ?? 'N/A';

$caption  = "🚨 Relatório de Canal\n\n";
$caption .= "Canal: $canal\n";
$caption .= "Problema: $erro\n";
$caption .= "URL: $url";

/* SE TEM ANEXO */
if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {

    $tmp  = $_FILES['anexo']['tmp_name'];
    $name = $_FILES['anexo']['name'];
    $type = $_FILES['anexo']['type']; // MAIS CONFIÁVEL

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
    curl_exec($ch);
    curl_close($ch);
}

echo "OK";
