<?php
$canal = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['canal'] ?? '');
$canais = include('listacanais.php');

if (!$canal || !isset($canais[$canal])) {
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

#btnTravou {
    position: fixed;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    padding: 6px 14px;
    border-radius: 10px;
    border: none;
    background: #111;
    color: #fff;
    cursor: pointer;
}

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

#statusMsg {
    margin-top: 8px;
    font-size: 12px;
    display: none;
    text-align: center;
}
</style>
</head>

<body>

<button id="btnTravou">Travou? Clique aqui</button>

<button id="btnReportar" title="Reportar problema">
    <i class="fa-solid fa-triangle-exclamation"></i>
</button>

<div id="reportBox">
    <strong>Reportar problema</strong>

    <label><input type="radio" name="motivo" value="Canal não está funcionando"> Canal não está funcionando</label>
    <label><input type="radio" name="motivo" value="Este canal não existe"> Este canal não existe</label>
    <label><input type="radio" name="motivo" value="Está travando"> Está travando</label>
    <label><input type="radio" name="motivo" value="Outros"> Outros</label>

    <textarea id="outrosTexto" placeholder="Descreva o problema..."></textarea>

    <button id="enviarReport">
        <i class="fa-solid fa-paper-plane"></i> Enviar
    </button>

    <div id="statusMsg"></div>
</div>

<iframe id="playerFrame"
    src="<?= htmlspecialchars($urlIframe, ENT_QUOTES) ?>"
    allow="encrypted-media"
    allowfullscreen
    scrolling="no">
</iframe>

<script>
const canal = <?= json_encode($canal) ?>;
const iframe = document.getElementById("playerFrame");

const reportBox = document.getElementById("reportBox");
const btnReportar = document.getElementById("btnReportar");
const btnEnviar = document.getElementById("enviarReport");
const outrosTexto = document.getElementById("outrosTexto");
const statusMsg = document.getElementById("statusMsg");

/* Abrir / fechar report */
btnReportar.onclick = () => {
    reportBox.style.display = reportBox.style.display === "block" ? "none" : "block";
};

/* Mostrar textarea apenas em Outros */
document.querySelectorAll('input[name="motivo"]').forEach(el => {
    el.onchange = () => {
        outrosTexto.style.display = el.value === "Outros" ? "block" : "none";
    };
});

/* Enviar report */
btnEnviar.onclick = () => {
    const motivo = document.querySelector('input[name="motivo"]:checked');
    if (!motivo) return;

    btnEnviar.disabled = true;
    statusMsg.style.display = "block";
    statusMsg.style.color = "#ccc";
    statusMsg.textContent = "Enviando...";

    let texto = motivo.value;
    if (texto === "Outros" && outrosTexto.value.trim()) {
        texto += " - " + outrosTexto.value.trim();
    }

    fetch("telegram_log.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            canal: canal,
            url: iframe.src,
            erro: texto
        })
    })
    .then(r => r.text())
    .then(resp => {
        if (resp.trim() === "OK") {
            statusMsg.style.color = "#00ff88";
            statusMsg.textContent = "Enviado com sucesso";
            outrosTexto.value = "";
        } else {
            throw new Error();
        }
    })
    .catch(() => {
        statusMsg.style.color = "#ff5555";
        statusMsg.textContent = "Erro ao enviar";
    })
    .finally(() => {
        btnEnviar.disabled = false;
        setTimeout(() => statusMsg.style.display = "none", 3000);
    });
};

/* Travou? apenas recarrega iframe (SEM LOG) */
document.getElementById("btnTravou").onclick = () => {
    iframe.src = iframe.src;
};
</script>

</body>
</html>
