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

<!-- Ícone -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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

/* Botão travou */
#btnTravou {
    position: fixed;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    padding: 5px 12px;
    font-size: 13px;
    font-weight: bold;
    border-radius: 10px;
    border: none;
    background: #000;
    color: #fff;
    cursor: pointer;
}

/* Botão denúncia */
#btnDenuncia {
    position: fixed;
    bottom: 15px;
    left: 15px;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #d10000;
    color: #fff;
    border: none;
    cursor: pointer;
    z-index: 9999;
    box-shadow: 0 0 10px rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

/* Modal */
#modalDenuncia {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.7);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

#modalDenuncia .box {
    background: #111;
    color: #fff;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    width: 90%;
    max-width: 320px;
}

#modalDenuncia button {
    margin: 10px 5px 0;
    padding: 6px 14px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: bold;
}

.btnSim {
    background: #d10000;
    color: #fff;
}

.btnNao {
    background: #444;
    color: #fff;
}
</style>

<script disable-devtool-auto src="https://cdn.jsdelivr.net/npm/disable-devtool@latest"></script>
</head>

<body>

<button id="btnTravou">Travou?</button>

<!-- Botão Denúncia -->
<button id="btnDenuncia" title="Denunciar canal">
    <i class="fa-solid fa-flag"></i>
</button>

<!-- Modal Denúncia -->
<div id="modalDenuncia">
    <div class="box">
        <p><strong>Deseja reportar este canal?</strong></p>
        <button class="btnSim">Sim</button>
        <button class="btnNao">Não</button>
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

// Enviar log Telegram
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

/* BOTÃO TRAVOU */
document.getElementById("btnTravou").onclick = () => {
    enviarLog("Usuário clicou em TRAVOU");
    iframe.src = iframe.src;
};

/* DENÚNCIA */
const btnDenuncia = document.getElementById("btnDenuncia");
const modal = document.getElementById("modalDenuncia");

btnDenuncia.onclick = () => {
    modal.style.display = "flex";
};

modal.querySelector(".btnNao").onclick = () => {
    modal.style.display = "none";
};

modal.querySelector(".btnSim").onclick = () => {
    enviarLog("🚩 CANAL DENUNCIADO PELO USUÁRIO");
    modal.style.display = "none";
};
</script>

</body>
</html>
