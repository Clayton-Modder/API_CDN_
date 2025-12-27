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

<meta name="robots" content="noindex, nofollow">
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

/* Botão Travou */
#btnTravou {
    position: fixed;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    padding: 6px 14px;
    font-weight: bold;
    border-radius: 10px;
    border: none;
    background: #000;
    color: #fff;
    cursor: pointer;
}

/* Botão Reportar */
#btnReportar {
    position: fixed;
    bottom: 15px;
    right: 15px;
    z-index: 9999;
    padding: 8px 14px;
    font-weight: bold;
    border-radius: 12px;
    border: none;
    background: #c00;
    color: #fff;
    cursor: pointer;
}

/* Caixa de Report */
#reportBox {
    display: none;
    position: fixed;
    bottom: 70px;
    right: 15px;
    width: 260px;
    background: #111;
    color: #fff;
    padding: 12px;
    border-radius: 12px;
    z-index: 10000;
    font-size: 14px;
}

#reportBox label {
    display: block;
    margin: 6px 0;
    cursor: pointer;
}

#reportBox textarea {
    width: 100%;
    height: 60px;
    margin-top: 5px;
    display: none;
    background: #222;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 5px;
}

#reportBox button {
    margin-top: 8px;
    width: 100%;
    border: none;
    padding: 6px;
    border-radius: 8px;
    background: #0a0;
    color: #fff;
    cursor: pointer;
}
</style>

<script>
if (window.top === window.self) {
    location.href = "https://google.com";
}
document.addEventListener('contextmenu', e => e.preventDefault());
</script>
</head>

<body>

<button id="btnTravou">Travou?</button>
<button id="btnReportar">Reportar</button>

<div id="reportBox">
    <strong>Qual o problema?</strong>

    <label><input type="radio" name="motivo" value="Canal fora do ar"> Canal fora do ar</label>
    <label><input type="radio" name="motivo" value="Sem áudio"> Sem áudio</label>
    <label><input type="radio" name="motivo" value="Travando muito"> Travando muito</label>
    <label><input type="radio" name="motivo" value="Tela preta"> Tela preta</label>
    <label><input type="radio" name="motivo" value="Outros"> Outros</label>

    <textarea id="outrosTexto" placeholder="Descreva o problema..."></textarea>

    <button id="enviarReport">Enviar</button>
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
const reportBox = document.getElementById("reportBox");
const btnReportar = document.getElementById("btnReportar");
const btnEnviar = document.getElementById("enviarReport");
const outrosTexto = document.getElementById("outrosTexto");

btnReportar.onclick = () => {
    reportBox.style.display = reportBox.style.display === "block" ? "none" : "block";
};

document.querySelectorAll('input[name="motivo"]').forEach(el => {
    el.addEventListener("change", () => {
        outrosTexto.style.display = el.value === "Outros" ? "block" : "none";
    });
});

function enviarLog(motivo) {
    fetch("telegram_log.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            canal: canal,
            url: iframe.src,
            erro: motivo
        })
    });
}

btnEnviar.onclick = () => {
    let motivo = document.querySelector('input[name="motivo"]:checked');
    if (!motivo) return alert("Selecione um motivo");

    let texto = motivo.value;
    if (texto === "Outros") {
        if (outrosTexto.value.trim() === "") {
            return alert("Descreva o problema");
        }
        texto += " - " + outrosTexto.value;
    }

    enviarLog(texto);
    reportBox.style.display = "none";
    outrosTexto.value = "";
    alert("Problema enviado com sucesso!");
};

// botão travou
document.getElementById("btnTravou").onclick = () => {
    enviarLog("Usuário clicou em TRAVOU");
    iframe.src = iframe.src;
};
</script>

</body>
</html>
