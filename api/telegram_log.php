<?php
$BOT_TOKEN = "7542514291:AAH9eZgl511x2slhR14G0sxoxRZ4Eo-Ho-M";
$CHAT_ID  = "-5237706159";
$SECRET_KEY = "0x4AAAAAACJO9U_HmfwW83hh47E_W-LMXBQ";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

/* VERIFICA CAPTCHA */
$captcha = $_POST['cf-turnstile-response'] ?? '';

$verify = curl_init("https://challenges.cloudflare.com/turnstile/v0/siteverify");
curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query([
    'secret' => $SECRET_KEY,
    'response' => $captcha,
    'remoteip' => $_SERVER['REMOTE_ADDR']
]));

$response = json_decode(curl_exec($verify), true);
curl_close($verify);

if (empty($response['success'])) {
    exit; // captcha inválido → ignora
}

/* DADOS */
$canal = $_POST['canal'] ?? 'desconhecido';
$url   = $_POST['url'] ?? 'sem_url';
$erro  = $_POST['erro'] ?? 'erro_nao_informado';

$ip    = $_SERVER['REMOTE_ADDR'];
$data  = date('d/m/Y H:i:s');

$msg = "🚩 *DENÚNCIA DE CANAL*\n\n".
       "📺 Canal: `$canal`\n".
       "🌐 URL: `$url`\n".
       "❌ Motivo:\n$erro\n".
       "📍 IP: `$ip`\n".
       "🕒 Data: `$data`";

$tgUrl = "https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage";

curl_setopt_array($ch = curl_init($tgUrl), [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'chat_id' => $CHAT_ID,
        'text' => $msg,
        'parse_mode' => 'Markdown'
    ],
    CURLOPT_RETURNTRANSFER => true
]);

curl_exec($ch);
curl_close($ch);
