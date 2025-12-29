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

/* Botão Travou */
#btnTravou {
    position: fixed;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    padding: 6px 16px;
    border-radius: 14px;
    border: none;
    background: #111;
    color: #fff;
    cursor: pointer;
}

/* Botão Reportar */
#btnReportar {
    position: fixed;
    bottom: 18px;
    left: 18px;
    width: 52px;
    height: 52px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg,#b00000,#ff3b3b);
    color: #fff;
    font-size: 22px;
    cursor: pointer;
    z-index: 9999;
    box-shadow: 0 8px 20px rgba(0,0,0,.6);
}

/* Report Box */
#reportBox {
    display: none;
    position: fixed;
    bottom: 90px;
    left: 18px;
    width: 310px;
    background: #0f0f0f;
    border-radius: 18px;
    padding: 16px;
    color: #fff;
    z-index: 10000;
    box-shadow: 0 0 30px rgba(0,0,0,.9);
    animation: fadeUp .2s ease;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

.report-header {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: bold;
    margin-bottom: 14px;
}

.report-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.motivo {
    background: #1a1a1a;
    border-radius: 14px;
    padding: 12px;
    text-align: center;
    cursor: pointer;
    font-size: 13px;
    transition: .2s;
    border: 1px solid #222;
}

.motivo i {
    display: block;
    font-size: 20px;
    margin-bottom: 6px;
    color: #ff5555;
}

.motivo:hover {
    background: #222;
}

.motivo.active {
    background: #5c0000;
    border-color: #ff3b3b;
}

#outrosTexto {
    display: none;
    margin-top: 10px;
    width: 100%;
    height: 70px;
    background: #1c1c1c;
    border-radius: 10px;
    border: none;
    padding: 8px;
    color: #fff;
    resize: none;
}

#enviarReport {
    margin-top: 14px;
    width: 100%;
    padding: 10px;
    border-radius: 14px;
    border: none;
    background: linear-gradient(135deg,#ff3b3b,#b00000);
    color: #fff;
    font-weight: bold;
    cursor: pointer;
}

#statusMsg {
    margin-top: 8px;
    text-align: center;
    font-size: 12px;
    display: none;
}

/* Iframe */
iframe {
    height: 100vh;
}
</style>
</head>

<body>

<button id="btnTravou">Travou? Clique aqui</button>

<button id="btnReportar" title="Reportar problema">
    <i class="fa-solid fa-triangle-exclamation"></i>
</button>

<div id="reportBox">
    <div class="report-header">
        <i class="fa-solid fa-bug"></i>
        <span>Reportar problema</span>
    </div>

    <div class="report-grid">
        <div class="motivo" data-value="Canal fora do ar">
            <i class="fa-solid fa-circle-xmark"></i>
            Canal fora do ar
        </div>
        <div class="motivo" data-value="Está travando">
            <i class="fa-solid fa-spinner"></i>
            Travando
        </div>
        <div class="motivo" data-value="Erro de áudio ou vídeo">
            <i class="fa-solid fa-volume-xmark"></i>
            Áudio / Vídeo
        </div>
        <div class="motivo" data-value="Canal inválido">
            <i class="fa-solid fa-ban"></i>
            Canal inválido
        </div>
        <div class="motivo" data-value="Outros">
            <i class="fa-solid fa-ellipsis"></i>
            Outros
        </div>
    </div>

    <textarea id="outrosTexto" placeholder="Descreva o problema..."></textarea>

    <button id="enviarReport">
        <i class="fa-solid fa-paper-plane"></i> Enviar report
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

let motivoSelecionado = "";

/* Abrir / fechar report */
btnReportar.onclick = () => {
    reportBox.style.display =
        reportBox.style.display === "block" ? "none" : "block";
};

/* Seleção de motivo */
document.querySelectorAll(".motivo").forEach(el => {
    el.onclick = () => {
        document.querySelectorAll(".motivo").forEach(m => m.classList.remove("active"));
        el.classList.add("active");

        motivoSelecionado = el.dataset.value;
        outrosTexto.style.display = motivoSelecionado === "Outros" ? "block" : "none";
    };
});

/* Enviar report */
btnEnviar.onclick = () => {
    if (!motivoSelecionado) return;

    let texto = motivoSelecionado;
    if (motivoSelecionado === "Outros" && outrosTexto.value.trim()) {
        texto += " - " + outrosTexto.value.trim();
    }

    btnEnviar.disabled = true;
    statusMsg.style.display = "block";
    statusMsg.style.color = "#ccc";
    statusMsg.textContent = "Enviando...";

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
            statusMsg.textContent = "Report enviado com sucesso";
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
        setTimeout(() => {
            statusMsg.style.display = "none";
            reportBox.style.display = "none";
            outrosTexto.value = "";
            motivoSelecionado = "";
            document.querySelectorAll(".motivo").forEach(m => m.classList.remove("active"));
        }, 2500);
    });
};

/* Travou? apenas recarrega iframe */
document.getElementById("btnTravou").onclick = () => {
    iframe.src = iframe.src;
};
</script>

</body>
</html>
