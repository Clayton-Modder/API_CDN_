<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$url = "https://tvonlinehd.com.br/channels.php";

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => [
        "User-Agent: Mozilla/5.0",
        "Accept: application/json"
    ]
]);

$response = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

// 🔥 DEBUG
if ($error) {
    echo json_encode([
        "error" => true,
        "type" => "cURL Error",
        "message" => $error
    ]);
    exit;
}

if ($httpCode !== 200) {
    echo json_encode([
        "error" => true,
        "type" => "HTTP Error",
        "status" => $httpCode,
        "url" => $url
    ]);
    exit;
}

// 🔥 tenta validar JSON
$data = json_decode($response, true);

if (!$data) {
    echo json_encode([
        "error" => true,
        "type" => "Invalid JSON",
        "raw" => substr($response, 0, 500)
    ]);
    exit;
}

// sucesso
echo $response;
