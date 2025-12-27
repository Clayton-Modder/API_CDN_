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

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<style>
html, body, iframe {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    background: #000;
    border: none;
    font-family: 'Inter', sans-serif;
}

/* Botão travou */
#btnTravou {
    position: fixed;
    top: 12px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    padding: 6px 14px;
    border-radius: 12px;
    border: none;
    background: #111;
    color: #fff;
    font-size: 13px;
    cursor: pointer;
}

/* Botão report (ícone) */
#btnReportar {
    position: fixed;
    bottom: 18px;
    left: 18px;
    z-index: 9999;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: none;
    background: #c00;
    color: #fff;
    font-size: 20px;
    cursor: pointer;
}

/* Caixa de reporte */
#reportBox {
    display: none;
    position: fixed;
    bottom: 75px;
    left: 18px;
    width: 280px;
    background: #111;
    color: #fff;
    padding: 16px;
    border-radius: 16px;
    z-index: 10000;
    box-shadow: 0 0 20px rgba(0,0,0,.6);
}

#reportBox h3 {
    margin: 0 0 10px;
    font-size: 16px;
    font-weight: 600;
}

#reportBox p {
    margin: 0 0 12px;
    font-size: 13px;
    color: #bbb;
}

/* Motivos */
.report-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    font-size: 14px;
    cursor: pointer;
}

.report-option input {
    accent-color: #c00;
}

/* Outros */
#outrosTexto {
    width: 100%;
    height: 70px;
    margin-top: 8px;
    display: none;
    background: #1c1c1c;
    color: #fff;
    border-radius: 8px;
    border: none;
    padding: 8px;
    font-size: 13px;
}

/* Enviar */
#enviarReport {
    margin-top: 10px;
    width: 100%;
    padding: 8px;
    border-radius: 10px;
    border: none;
    background: #0a0;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

/* Status */
#statusMsg {
    margin-top: 8px;
    font-size: 12px;
    color: #0f0;
    display: none;
    text-align: center;
}
</style>
</head>

<body>

<button id="btnTravou">Travou?</button>
<button id="btnReportar" title="Reportar problema">⚠️</button>

<div id="reportBox">
    <h3>Reportar problema</h3>
    <p>Selecione o motivo abaixo:</p>

    <label class="report-option">
        <input type="radio" name="motivo" value="Canal não está funcionando">
        Canal não está funcionando
    </label>

    <label class="report-option">
        <input type="radio" name="motivo" value="Este canal não existe">
        Este canal não existe
    </label>

    <label class="report-option">
        <input type="radio" name="motivo" value="Está travando">
        Está travando
    </label>

    <label class="report-option">
        <input type="radio" name="motivo" value="Outros">
        Outros
    </label>

    <textarea id="outrosTexto" placeholder="Descreva o problema..."></textarea>

    <button id="enviarReport">Enviar reporte</button>
    <div id="statusMsg">Reporte enviado com sucesso ✓</div>
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

const btnReportar = document.getElementById("btnReportar");
const reportBox = document.getElementById("reportBox");
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

// Travou
document.getElementById("btnTravou").onclick = () => {
    enviarLog("Usuário clicou em TRAVOU");
    iframe.src = iframe.src;
};
</script>

</body>
</html>
