<?php
$canal = $_GET['canal'] ?? null;
$canais = include('listacanais.php');

if (!isset($canais[$canal])) {
    exit("Este canal não existe");
}

$urlIframe = $canais[$canal];
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<meta name="robots" content="noindex, nofollow, noarchive">
<meta name="googlebot" content="noindex, nofollow, noarchive">
<meta name="referrer" content="no-referrer">

<title>Player</title>

<style>
html, body, iframe {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    background: #000;
    border: none;
    overflow: hidden;
}

#btnTravou {
    position: fixed;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    padding: 6px 14px;
    font-size: 14px;
    font-weight: bold;
    border-radius: 10px;
    border: none;
    background: #000;
    color: #fff;
    cursor: pointer;
}
</style>

<script disable-devtool-auto src="https://cdn.jsdelivr.net/npm/disable-devtool@latest"></script>

<script>
if (window.top === window.self) {
    location.href = "https://google.com";
}

document.addEventListener('contextmenu', e => e.preventDefault());

document.onkeydown = function(e) {
    if (e.ctrlKey || e.keyCode === 123) {
        return false;
    }
};
</script>
</head>

<body>

<button id="btnTravou">Travou? Clique aqui</button>

<iframe id="playerFrame"
    src="<?php echo $urlIframe; ?>"
    allow="encrypted-media"
    allowfullscreen
    scrolling="no">
</iframe>

<script>
const canal = "<?php echo $canal; ?>";
const iframe = document.getElementById("playerFrame");
const botao = document.getElementById("btnTravou");

let reloads = 0;

function enviarLog(motivo) {
    fetch("telegram_log.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            canal: canal,
            url: iframe.src,
            erro: motivo
        })
    });
}

// Erro ao carregar iframe
iframe.addEventListener("error", () => {
    enviarLog("Erro ao carregar iframe");
});

// Loop de carregamento
iframe.onload = () => {
    reloads++;
    if (reloads > 5) {
        enviarLog("Loop excessivo de carregamento");
    }
};

// Botão travou
botao.addEventListener("click", () => {
    enviarLog("Usuário clicou em TRAVOU");
    iframe.src = iframe.src;
});
</script>

</body>
</html>
