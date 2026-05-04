
function abrirModalLiberar(idPiloto, nombre, escuderia, devolucion) {
    document.getElementById('liberarIdPiloto').value  = idPiloto;
    document.getElementById('liberarNombre').textContent    = nombre;
    document.getElementById('liberarEscuderia').textContent = escuderia;
    document.getElementById('liberarDevolucion').textContent = devolucion;
    document.getElementById('modalLiberar').classList.add('abierto');
}

function abrirModalFicharEscuderia() {
    document.getElementById('modalFicharEscuderia').classList.add('abierto');
}

function abrirModalLiberarEscuderia(idEsc, nombre, devolucion) {
    document.getElementById('liberarEscId').value = idEsc;
    document.getElementById('liberarEscNombre').textContent = nombre;
    document.getElementById('liberarEscDevolucion').textContent = devolucion;
    document.getElementById('modalLiberarEscuderia').classList.add('abierto');
}

function abrirModal(slot) {
    var labels = { 1: 'Slot 1 // Capitán ×2', 2: 'Slot 2 // Segundo Piloto' };
    document.getElementById('modalSlotLabel').textContent = labels[slot] || ('Slot ' + slot);
    document.querySelectorAll('.input-slot-modal').forEach(function(inp) {
        inp.value = slot;
    });
    document.getElementById('modalSlot').classList.add('abierto');
}

function abrirModalFichar() {
    document.getElementById('modalFichar').classList.add('abierto');
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('abierto');
}

document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) overlay.classList.remove('abierto');
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.abierto').forEach(function(m) {
            m.classList.remove('abierto');
        });
    }
});

(function () {
    var el = document.getElementById('countdown');
    if (!el) return;
    var obj = parseInt(el.dataset.ts) * 1000;
    function pad(n) { return String(n).padStart(2, '0'); }
    function actualizar() {
        var diff = Math.max(0, obj - Date.now());
        if (diff <= 0) { el.textContent = '¡En pista!'; return; }
        var d = Math.floor(diff / 86400000);
        var h = Math.floor((diff % 86400000) / 3600000);
        var m = Math.floor((diff % 3600000)  / 60000);
        var s = Math.floor((diff % 60000)    / 1000);
        el.textContent = pad(d) + 'D : ' + pad(h) + 'H : ' + pad(m) + 'M : ' + pad(s) + 'S';
        setTimeout(actualizar, 1000);
    }
    actualizar();
})();
