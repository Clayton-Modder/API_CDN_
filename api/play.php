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
    padding: 5px 12px;
    font-size: 13px;
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
    padding: 18px;
    border-radius: 12px;
    width: 95%;
    max-width: 350px;
}

#modalDenuncia label {
    display: block;
    margin: 6px 0;
    font-size: 14px;
    cursor: pointer;
}

#modalDenuncia textarea {
    width: 100%;
    margin-top: 8px;
    background: #222;
    color: #fff;
    border: 1px solid #444;
    border-radius: 6px;
    padding: 6px;
    resize: none;
    display: none;
}

.modalBtns {
    text-align: center;
    margin-top: 12px;
}

.modalBtns button {
    padding: 6px 14px;
    border-radius: 8px;
    border: none;
    font-weight: bold;
    cursor: pointer;
}

.btnEnviar {
    background: #d10000;
    color: #fff;
}

.btnCancelar {
    background: #444;
    color: #fff;
}
</style>
</head>

<body>

<button id="btnTravou">Travou?</button>

<button id="btnDenuncia" title="Denunciar canal">
    <i class="fa-solid fa-flag"></i>
</button>

<!-- MODAL DENÚNCIA -->
<div id="modalDenuncia">
    <div class="box">
        <strong>Deseja reportar este canal?</strong>

        <form id="formDenuncia">
            <label><input type="radio" name="motivo" value="Não está funcionando" required> Não está funcionando</label>
            <label><input type="radio" name="motivo" value="Canal está travando"> Canal está travando</label>
            <label><input type="radio" name="motivo" value="Muito anúncio"> Muito anúncio</label>
            <label><input type="radio" name="motivo" value="Erro: canal não existe"> Erro: canal não existe</label>
            <label><input type="radio" name="motivo" value="Outro"> Outro</label>

            <textarea id="descricao" rows="3" placeholder="Descreva o problema..."></textarea>

            <div class="modalBtns">
                <button type="submit" class="btnEnviar">Enviar</button>
                <button type="button" class="btnCancelar">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<iframe id="playerFrame"
    src="<?php echo $urlIframe; ?>"
    allowfullscreen
    scrolling="no">
</iframe>

<script>
const canal = "<?php echo $canal; ?>";
const iframe = document.getElementById("playerFrame");
const modal = document.getElementById("modalDenuncia");
const descricao = document.getElementById("descricao");

/* Função de envio Telegram (somente denúncia) */
function enviarLog(motivo) {
    fetch("telegram_log.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: new URLSearchParams({
            canal: canal,
            url: iframe.src,
            erro: motivo
        })
    });
}

/* BOTÃO TRAVOU → SOMENTE RECARREGA */
document.getElementById("btnTravou").onclick = () => {
    iframe.src = iframe.src;
};

/* Abrir modal denúncia */
document.getElementById("btnDenuncia").onclick = () => {
    modal.style.display = "flex";
};

/* Cancelar */
document.querySelector(".btnCancelar").onclick = () => {
    modal.style.display = "none";
    descricao.style.display = "none";
    descricao.value = "";
};

/* Mostrar textarea se Outro */
document.querySelectorAll('input[name="motivo"]').forEach(el => {
    el.onchange = () => {
        descricao.style.display = (el.value === "Outro") ? "block" : "none";
    };
});

/* Enviar denúncia */
document.getElementById("formDenuncia").onsubmit = e => {
    e.preventDefault();

    const motivo = document.querySelector('input[name="motivo"]:checked').value;
    const texto = descricao.value.trim();

    let msg = "🚩 DENÚNCIA DE CANAL\nMotivo: " + motivo;
    if (motivo === "Outro" && texto) {
        msg += "\nDescrição: " + texto;
    }

    enviarLog(msg);
    modal.style.display = "none";
    descricao.value = "";
};
</script>

</body>
</html>
