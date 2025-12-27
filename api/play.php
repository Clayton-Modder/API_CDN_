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
}

#reportBox {
    position: fixed;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    background: rgba(0,0,0,0.85);
    padding: 8px;
    border-radius: 10px;
    display: flex;
    gap: 5px;
}

#reportBox select,
#reportBox button {
    font-size: 13px;
    border-radius: 6px;
    border: none;
    padding: 6px;
}

#reportBox button {
    background: #e50914;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
}
</style>

<script disable-devtool-auto src="https://cdn.jsdelivr.net/npm/disable-devtool@latest"></script>

<script>
if (window.top === window.self) {
    location.href = "https://google.com";
}

document.addEventListener('contextmenu', e => e.preventDefault());
document.onkeydown = e => (e.ctrlKey || e.keyCode === 123) ? false : true;
</script>
</head>

<body>

<!-- BOX DE REPORT -->
<div id="reportBox">
    <select id="motivo">
        <option value="">⚠️ Reportar problema</option>
        <option value="Canal offline">Canal offline</option>
        <option value="Tela preta">Tela preta</option>
        <option value="Sem áudio">Sem áudio</option>
        <option value="Travando muito">Travando muito</option>
        <option value="Não carrega">Não carrega</option>
        <option value="Outro problema">Outro problema</option>
    </select>
    <button id="btnReportar">Enviar</button>
</div>

<iframe id="playerFrame"
    src="<?php echo $urlIframe; ?>"
    allow="encrypted-media"
    allowfullscreen
    scrolling="no">
</iframe>

<script>
const canal = "<?php echo $canal; ?>";
const iframe = document.getElementById("playerFrame");

document.getElementById("btnReportar").addEventListener("click", () => {
    const motivo = document.getElementById("motivo").value;

    if (!motivo) {
        alert("Selecione um motivo");
        return;
    }

    fetch("telegram_log.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            canal: canal,
            url: iframe.src,
            erro: motivo
        })
    });

    iframe.src = iframe.src; // recarrega player
    alert("Problema enviado. Obrigado!");
});
</script>

</body>
</html>
