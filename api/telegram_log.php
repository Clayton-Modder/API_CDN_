
<?php
$token  = "8553413416:AAETpmhLL933bqSv__RsVtp9j8RJI1D3WjI";
$chatId = "-5286992033";

$canal = $_POST['canal'] ?? 'N/A';
$url   = $_POST['url'] ?? 'N/A';
$erro  = $_POST['erro'] ?? 'N/A';

$caption  = "🚨 *Relatório de Canal*\n\n";
$caption .= "📺 Canal: `$canal`\n";
$caption .= "❌ Problema: $erro\n";
$caption .= "🔗 URL: $url";

if (!empty($_FILES['anexo']['tmp_name'])) {

    $fileTmp  = $_FILES['anexo']['tmp_name'];
    $fileName = $_FILES['anexo']['name'];
    $mime     = mime_content_type($fileTmp);

    if (strpos($mime, 'image/') === 0) {
        $endpoint = "sendPhoto";
        $field = "photo";
    } else {
        $endpoint = "sendDocument";
        $field = "document";
    }

    $urlApi = "https://api.telegram.org/bot$token/$endpoint";

    $post = [
        'chat_id' => $chatId,
        'caption' => $caption,
        'parse_mode' => 'Markdown',
        $field => new CURLFile($fileTmp, $mime, $fileName)
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlApi);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

} else {

    file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query([
        'chat_id' => $chatId,
        'text' => $caption,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true
    ]));
}

echo "OK";
