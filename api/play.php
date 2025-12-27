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

/* Botão Travou */
#btnTravou {
    position: fixed;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    padding: 6px 14px;
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
    border-radius: 14px;
    border: none;
    background: #c00;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: bold;
}

#btnReportar i {
    font-size: 16px;
}

/* Caixa Report */
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
    background: #222;
    color: #fff;
    border-radius: 6px;
    border: none;
    padding: 5px;
}

#enviarReport {
    margin-top: 8px;
    width: 100%;
    border: none;
    padding: 6px;
    border-radius: 8px;
    background: #0a0;
    color: #fff;
    cursor: pointer;
}

#statusMsg {
    margin-top: 6px;
    font-size: 12px;
    color: #0f0;
    display: none;
    text-align: center;
}
</style>
</head>

<body>

<button id="btnTravou">Travou?</button>

<button id="btnReportar">
    <i class="fa-solid fa-triangle-exclamation"></i>
    Reportar
</button>

<div id="reportBox">
    <strong>Selecione o problema</strong>

    <label><input type="radio" name="motivo" value="Canal não está funcionando"> Canal não está funcionando</label>
    <label><input type="radio" name="motivo" value="Este canal não existe"> Este canal não existe</label>
    <label><input type="radio" name="motivo" value="Está travando"> Está travando</label>
    <label><input type="radio" name="motivo" value="Outros"> Outros</label>

    <textarea id="outrosTexto" placeholder="Descreva o problema..."></textarea>

    <button id="enviarReport">Enviar</button>
    <div id="statusMsg">Enviado com sucesso ✓</div>
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

// botão travou
document.getElementById("btnTravou").onclick = () => {
    enviarLog("Usuário clicou em TRAVOU");
    iframe.src = iframe.src;
};
</script>

</body>
</html>
