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

<title>Player</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

<style>
html, body, iframe {
    margin: 0; padding: 0;
    width: 100%; height: 100%;
    background: #000;
    border: none;
}

#btnTravou {
    position: fixed;
    top: 10px; left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    padding: 5px 12px;
    font-size: 13px;
    border-radius: 10px;
    background: #000;
    color: #fff;
    border: none;
}

#btnDenuncia {
    position: fixed;
    bottom: 15px; left: 15px;
    width: 42px; height: 42px;
    border-radius: 50%;
    background: #d10000;
    color: #fff;
    border: none;
    font-size: 18px;
    z-index: 9999;
}

#modalDenuncia {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.7);
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
    max-width: 360px;
}

#modalDenuncia textarea {
    width: 100%;
    margin-top: 8px;
    display: none;
    background: #222;
    color: #fff;
    border: 1px solid #444;
    border-radius: 6px;
}

.modalBtns {
    text-align: center;
    margin-top: 10px;
}
.modalBtns button {
    padding: 6px 14px;
    border-radius: 8px;
    border: none;
    font-weight: bold;
}
.btnEnviar { background: #d10000; color: #fff; }
.btnCancelar { background: #444; color: #fff; }
</style>
</head>

<body>

<button id="btnTravou">Travou?</button>

<button id="btnDenuncia"><i class="fa-solid fa-flag"></i></button>

<div id="modalDenuncia">
<div class="box">
<strong>Reportar canal</strong>

<form id="formDenuncia">
<label><input type="radio" name="motivo" value="Não está funcionando" required> Não está funcionando</label>
<label><input type="radio" name="motivo" value="Canal está travando"> Canal está travando</label>
<label><input type="radio" name="motivo" value="Muito anúncio"> Muito anúncio</label>
<label><input type="radio" name="motivo" value="Erro: canal não existe"> Erro: canal não existe</label>
<label><input type="radio" name="motivo" value="Outro"> Outro</label>

<textarea id="descricao" rows="3" placeholder="Descreva o problema..."></textarea>

<!-- CAPTCHA CLOUDFLARE -->
<div class="cf-turnstile" data-sitekey="SEU_SITE_KEY_AQUI"></div>

<div class="modalBtns">
<button type="submit" class="btnEnviar">Enviar</button>
<button type="button" class="btnCancelar">Cancelar</button>
</div>
</form>
</div>
</div>

<iframe id="playerFrame" src="<?php echo $urlIframe; ?>" allowfullscreen></iframe>

<script>
const iframe = document.getElementById("playerFrame");
const modal = document.getElementById("modalDenuncia");
const descricao = document.getElementById("descricao");

/* Travou → só recarrega */
document.getElementById("btnTravou").onclick = () => iframe.src = iframe.src;

/* Abrir modal */
document.getElementById("btnDenuncia").onclick = () => modal.style.display = "flex";

/* Cancelar */
document.querySelector(".btnCancelar").onclick = () => {
    modal.style.display = "none";
    descricao.style.display = "none";
    descricao.value = "";
};

/* Mostrar descrição */
document.querySelectorAll('input[name="motivo"]').forEach(r => {
    r.onchange = () => descricao.style.display = (r.value === "Outro") ? "block" : "none";
});

/* Enviar */
document.getElementById("formDenuncia").onsubmit = e => {
    e.preventDefault();

    const motivo = document.querySelector('input[name="motivo"]:checked').value;
    let texto = "Motivo: " + motivo;
    if (motivo === "Outro" && descricao.value.trim()) {
        texto += "\nDescrição: " + descricao.value.trim();
    }

    const formData = new FormData(e.target);
    formData.append("canal", "<?php echo $canal; ?>");
    formData.append("url", iframe.src);
    formData.append("erro", texto);

    fetch("telegram_log.php", { method: "POST", body: formData });
    modal.style.display = "none";
};
</script>

</body>
</html>
