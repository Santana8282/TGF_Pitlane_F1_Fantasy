function actualizarFuerza(v) {
    var b=document.getElementById('barraFuerza'),t=document.getElementById('textoFuerza');
    if(!v){b.style.width='0%';t.textContent='';return;}
    var p=0;
    if(v.length>=8)p++;if(v.length>=12)p++;
    if(/[A-Z]/.test(v))p++;if(/[0-9]/.test(v))p++;if(/[^A-Za-z0-9]/.test(v))p++;
    var n=[
        {w:'20%',c:'#f20d20',e:'Muy débil'},{w:'40%',c:'#f97316',e:'Débil'},
        {w:'60%',c:'#eab308',e:'Moderada'},{w:'80%',c:'#84cc16',e:'Fuerte'},
        {w:'100%',c:'#22c55e',e:'¡Contraseña de campeón!'}
    ][Math.min(p-1,4)];
    b.style.width=n.w;b.style.backgroundColor=n.c;
    t.textContent=n.e;t.style.color=n.c;
}
