<?php
$canal = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['canal'] ?? '');
$canais = include('listacanais.php');

if (!$canal || !isset($canais[$canal])) {
    exit("Canal não existe");
}
$urlIframe = $canais[$canal];
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Player</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
html,body,iframe{margin:0;padding:0;width:100%;height:100%;background:#000}
iframe{border:none}

/* Botões */
#btnTravou{
    position:fixed;top:10px;left:50%;transform:translateX(-50%);
    background:#111;color:#fff;border:none;padding:6px 16px;
    border-radius:14px;z-index:9999;cursor:pointer
}
#btnReportar{
    position:fixed;bottom:18px;left:18px;
    width:52px;height:52px;border-radius:50%;
    background:linear-gradient(135deg,#b00000,#ff3b3b);
    color:#fff;border:none;font-size:22px;
    z-index:9999;cursor:pointer
}

/* Box */
#reportBox{
    display:none;position:fixed;bottom:90px;left:18px;
    width:320px;background:#0f0f0f;color:#fff;
    border-radius:18px;padding:14px;z-index:10000
}
.report-grid{
    display:grid;grid-template-columns:1fr 1fr;gap:10px
}
.motivo{
    background:#1b1b1b;border-radius:12px;padding:10px;
    text-align:center;font-size:13px;cursor:pointer
}
.motivo.active{background:#5c0000}
.motivo i{display:block;font-size:18px;margin-bottom:4px;color:#ff5555}

#outrosTexto{
    display:none;margin-top:8px;width:100%;height:60px;
    background:#222;border:none;border-radius:8px;
    color:#fff;padding:6px
}

/* Captcha */
#captchaBox{
    margin-top:10px;background:#1a1a1a;
    border-radius:10px;padding:10px;text-align:center
}
.captcha-area{
    display:flex;justify-content:space-between;margin-top:8px
}
#captchaDrag,#captchaTarget{
    width:42px;height:42px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:18px
}
#captchaDrag{background:#8b0000;color:#fff;cursor:grab}
#captchaTarget{border:2px dashed #555;color:#0f0}
#captchaBox.ok #captchaTarget{background:#00aa55;border:none;color:#fff}

/* Enviar */
#enviarReport{
    margin-top:10px;width:100%;padding:10px;
    border:none;border-radius:14px;
    background:linear-gradient(135deg,#ff3b3b,#8b0000);
    color:#fff;font-weight:bold;cursor:pointer
}
#statusMsg{margin-top:6px;font-size:12px;text-align:center;display:none}
</style>
</head>

<body>

<button id="btnTravou">Travou? Clique aqui</button>
<button id="btnReportar"><i class="fa-solid fa-triangle-exclamation"></i></button>

<div id="reportBox">
    <strong><i class="fa-solid fa-bug"></i> Reportar problema</strong>

    <div class="report-grid">
        <div class="motivo" data="Canal não está funcionando"><i class="fa-solid fa-circle-xmark"></i>Canal fora do ar</div>
        <div class="motivo" data="Está travando"><i class="fa-solid fa-spinner"></i>Travando</div>
        <div class="motivo" data="Canal não existe"><i class="fa-solid fa-ban"></i>Canal inválido</div>
        <div class="motivo" data="Erro de áudio ou vídeo"><i class="fa-solid fa-volume-xmark"></i>Áudio/Vídeo</div>
        <div class="motivo" data="Outros"><i class="fa-solid fa-ellipsis"></i>Outros</div>
    </div>

    <textarea id="outrosTexto" placeholder="Descreva o problema"></textarea>

    <input type="file" id="anexo" accept="image/*" style="margin-top:8px;color:#ccc">

    <div id="captchaBox">
        Arraste o ícone para o alvo
        <div class="captcha-area">
            <div id="captchaDrag"><i class="fa-solid fa-bug"></i></div>
            <div id="captchaTarget"><i class="fa-solid fa-flag-checkered"></i></div>
        </div>
    </div>

    <button id="enviarReport"><i class="fa-solid fa-paper-plane"></i> Enviar</button>
    <div id="statusMsg"></div>
</div>

<iframe id="playerFrame" src="<?=htmlspecialchars($urlIframe)?>" allowfullscreen></iframe>

<script>
const box=document.getElementById("reportBox");
btnReportar.onclick=()=>box.style.display=box.style.display=="block"?"none":"block";

let motivo="",captchaOK=false;

document.querySelectorAll(".motivo").forEach(m=>{
 m.onclick=()=>{
  document.querySelectorAll(".motivo").forEach(x=>x.classList.remove("active"));
  m.classList.add("active");
  motivo=m.getAttribute("data");
  outrosTexto.style.display=motivo=="Outros"?"block":"none";
 }
});

captchaDrag.draggable=true;
captchaDrag.ondragstart=e=>e.dataTransfer.setData("ok","1");
captchaTarget.ondragover=e=>e.preventDefault();
captchaTarget.ondrop=e=>{
 e.preventDefault();captchaOK=true;captchaBox.classList.add("ok");
};

enviarReport.onclick=()=>{
 if(!motivo||!captchaOK)return;
 let texto=motivo;
 if(motivo=="Outros"&&outrosTexto.value)texto+=" - "+outrosTexto.value;

 let fd=new FormData();
 fd.append("canal","<?=$canal?>");
 fd.append("url",playerFrame.src);
 fd.append("erro",texto);
 if(anexo.files[0])fd.append("foto",anexo.files[0]);

 statusMsg.style.display="block";
 statusMsg.textContent="Enviando...";

 fetch("telegram_log.php",{method:"POST",body:fd})
 .then(r=>r.text())
 .then(r=>{
   statusMsg.textContent=r=="OK"?"Enviado com sucesso":"Erro ao enviar";
   statusMsg.style.color=r=="OK"?"#0f0":"#f55";
 });
};

btnTravou.onclick=()=>playerFrame.src=playerFrame.src;
</script>
</body>
</html>
