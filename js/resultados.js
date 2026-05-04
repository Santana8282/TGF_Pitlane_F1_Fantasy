var tablaPos = {1:25,2:18,3:15,4:12,5:10,6:8,7:6,8:4,9:2,10:1};
function calcularPreview() {
    var pos=parseInt(document.getElementById('inp_pos')?.value)||0;
    var sal=parseInt(document.getElementById('inp_sal')?.value)||0;
    var estado=document.getElementById('inp_estado')?.value||'finished';
    var vr=document.getElementById('inp_vr')?.checked?1:0;
    var pole=document.getElementById('inp_pole')?.checked?1:0;
    var sector=document.getElementById('inp_sector')?.checked?1:0;
    var ba=parseInt(document.getElementById('inp_ba')?.value)||0;
    var br=parseInt(document.getElementById('inp_br')?.value)||0;
    var pen=parseInt(document.getElementById('inp_pen')?.value)||0;
    var total=0,det=[];
    if(pos>=1&&pos<=10){total+=tablaPos[pos];det.push('P'+pos+': +'+(tablaPos[pos]));}
    else if(pos>=11&&pos<=15){total+=2;det.push('P'+pos+': +2');}
    if(estado==='finished'&&pos>0){total+=2;det.push('Termina: +2');}
    if(estado==='dnf'){total-=10;det.push('DNF: -10');}
    if(estado==='dsq'){total-=20;det.push('DSQ: -20');}
    if(vr){total+=10;det.push('VR: +10');}
    if(pole){total+=10;det.push('Pole: +10');}
    if(sector){total+=3;det.push('Sector: +3');}
    if(sal>0&&pos>0){var adel=sal-pos;if(adel>0){total+=adel*2;det.push('+'+adel+' pos: +'+(adel*2));}else if(adel<0){var p=Math.max(adel,-10);total+=p;det.push(adel+' pos: '+p);}}
    if(ba>0){total-=ba*5;det.push('🟡x'+ba+': -'+(ba*5));}
    if(br>0){total-=br*15;det.push('🔴x'+br+': -'+(br*15));}
    if(pen>0){total-=pen*5;det.push('Pen x'+pen+': -'+(pen*5));}
    var el=document.getElementById('previewPts');
    var elD=document.getElementById('previewDetalle');
    if(el){el.textContent=(total>=0?'+':'')+total+' pts';el.className='res-preview-valor '+(total>0?'pts-pos':total<0?'pts-neg':'');}
    if(elD)elD.textContent=det.join(' · ');
}
['inp_pos','inp_sal','inp_estado','inp_vr','inp_pole','inp_sector','inp_ba','inp_br','inp_pen'].forEach(function(id){
    var el=document.getElementById(id);
    if(el)el.addEventListener('change',calcularPreview);
    if(el)el.addEventListener('input',calcularPreview);
});
var datosFila=null;
function abrirEditar(datos){datosFila=datos;document.getElementById('modalEditarNombre').textContent=datos.piloto+' — '+(datos.escuderia||'');document.getElementById('modalEditar').classList.add('abierto');}
function copiarAlFormulario(){
    if(!datosFila)return;var d=datosFila;
    var setVal=function(id,val){var el=document.getElementById(id);if(el)el.value=val||'';};
    var setChk=function(id,val){var el=document.getElementById(id);if(el)el.checked=!!parseInt(val);};
    var sel=document.getElementById('sel_piloto');
    if(sel){for(var i=0;i<sel.options.length;i++){if(sel.options[i].value==d.id_piloto){sel.selectedIndex=i;break;}}}
    setVal('inp_pos',d.posicion);setVal('inp_sal',d.posicion_salida);setVal('inp_estado',d.estado||'finished');
    setVal('inp_pto',d.puntos_oficiales);setVal('inp_ba',d.banderas_amarillas);setVal('inp_br',d.banderas_rojas);
    setVal('inp_pen',d.penalizaciones);setChk('inp_vr',d.vuelta_rapida);setChk('inp_pole',d.pole_position);setChk('inp_sector',d.mejor_sector);
    cerrarModal('modalEditar');calcularPreview();
    var form=document.getElementById('formResultado');if(form)form.scrollIntoView({behavior:'smooth',block:'start'});
}
function cerrarModal(id){document.getElementById(id).classList.remove('abierto');}
document.querySelectorAll('.modal-overlay').forEach(function(o){o.addEventListener('click',function(e){if(e.target===o)o.classList.remove('abierto');});});
(function(){
    var banner=document.getElementById('autosync-banner');
    function mostrar(msg,ok){banner.textContent=msg;banner.style.display='block';banner.style.borderLeftColor=ok?'#27ae60':'#f2141f';setTimeout(function(){banner.style.display='none';},5000);}
    fetch('../private/autosync.php?ajax=1').then(function(r){return r.json();}).then(function(data){if(data.sincronizadas&&data.sincronizadas.length>0){mostrar('⟳ Sincronizado: '+data.sincronizadas.join(', '),true);setTimeout(function(){location.reload();},2000);}}).catch(function(){});
})();
