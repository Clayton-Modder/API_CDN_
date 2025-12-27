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

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
html, body, iframe {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    background: #000;
    border: none;
}

/* Botão Reportar (ESQUERDA) */
#btnReportar {
    position: fixed;
    bottom: 18px;
    left: 18px;
    z-index: 9999;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: none;
    background: #8b0000;
    color: #fff;
    cursor: pointer;
    font-size: 20px;
    box-shadow: 0 0 10px rgba(0,0,0,.6);
}

#btnReportar:hover {
    background: #a00000;
}

/* Caixa Report */
#reportBox {
    display: none;
    position: fixed;
    bottom: 80px;
    left: 18px;
    width: 270px;
    background: #111;
    color: #fff;
    padding: 14px;
    border-radius: 14px;
    z-index: 10000;
    font-size: 14px;
    box-shadow: 0 0 15px rgba(0,0,0,.8);
}

#reportBox strong {
    display: block;
    margin-bottom: 8px;
}

#reportBox label {
    display: block;
    margin: 6px 0;
    cursor: pointer;
}

#reportBox textarea {
    width: 100%;
    height: 60px;
    display: none;
    margin-top: 6px;
    background: #1c1c1c;
    color: #fff;
    border-radius: 6px;
    border: none;
    padding: 6px;
    resize: none;
}

/* Botão Enviar */
#enviarReport {
    margin-top: 10px;
    width: 100%;
    border: none;
    padding: 8px;
    border-radius: 10px;
    background: #5c0000;
    color: #fff;
    cursor: pointer;
    font-weight: bold;
}

#enviarReport:hover {
    background: #7a0000;
}

/* Status */
#statusMsg {
    margin-top: 8px;
    font-size: 12px;
    color: #00ff88;
    display: none;
    text-align: center;
}
</style>
</head>

<body>

<button id="btnReportar" title="Reportar problema">
    <i class="fa-solid fa-triangle-exclamation"></i>
</button>

<div id="reportBox">
    <strong><i class="fa-solid fa-bug"></i> Reportar problema</strong>

    <label><input type="radio" name="motivo" value="Canal não está funcionando"> Canal não está funcionando</label>
    <label><input type="radio" name="motivo" value="Este canal não existe"> Este canal não existe</label>
    <label><input type="radio" name="motivo" value="Está travando"> Está travando</label>
    <label><input type="radio" name="motivo" value="Outros"> Outros</label>

    <textarea id="outrosTexto" placeholder="Descreva o problema..."></textarea>

    <button id="enviarReport">
        <i class="fa-solid fa-paper-plane"></i> Enviar
    </button>

    <div id="statusMsg">
        <i class="fa-solid fa-circle-check"></i> Enviado com sucesso
    </div>
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
const statusMsg = document.getElementById("statusMsg");

btnReportar.onclick = () => {
    reportBox.style.display = reportBox.style.display === "block" ? "none" : "block";
};

document.querySelectorAll('input[name="motivo"]').forEach(el => {
    el.onchange = () => {
        outrosTexto.style.display = el.value === "Outros" ? "block" : "none";
    };
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
    const motivo = document.querySelector('input[name="motivo"]:checked');
    if (!motivo) return;

    let texto = motivo.value;
    if (texto === "Outros" && outrosTexto.value.trim() !== "") {
        texto += " - " + outrosTexto.value.trim();
    }

    enviarLog(texto);

    statusMsg.style.display = "block";
    setTimeout(() => statusMsg.style.display = "none", 3000);

    outrosTexto.value = "";
};
</script>

</body>
</html>
