function copiarCodigo(){
    var c=document.getElementById('ajCodigo').textContent.trim();
    var b=document.querySelector('.aj-btn-copiar');
    if(navigator.clipboard){navigator.clipboard.writeText(c).then(function(){b.textContent='Copiado';b.classList.add('copiado');setTimeout(function(){b.textContent='Copiar';b.classList.remove('copiado');},2500);});}
    else{var t=document.createElement('input');t.value=c;document.body.appendChild(t);t.select();document.execCommand('copy');document.body.removeChild(t);}
}
function abrirTransferir(id,nombre){
    document.getElementById('transferirIdEquipo').value=id;
    document.getElementById('transferirNombre').textContent=nombre;
    document.getElementById('modalTransferir').classList.add('abierto');
}
function cerrarTransferir(){document.getElementById('modalTransferir').classList.remove('abierto');}
document.getElementById('modalTransferir').addEventListener('click',function(e){if(e.target===this)cerrarTransferir();});
