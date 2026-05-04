(function () {
    var el = document.getElementById('countdown-index');
    if (!el) return;
    var ts = parseInt(el.dataset.ts) * 1000;
    function pad(n) { return String(n).padStart(2, '0'); }
    function actualizar() {
        var diff = Math.max(0, ts - Date.now());
        if (diff <= 0) {
            document.getElementById('cd-dias').textContent  = '¡En';
            document.getElementById('cd-horas').textContent = 'pista!';
            document.getElementById('cd-min').textContent   = '';
            document.getElementById('cd-seg').textContent   = '';
            return;
        }
        document.getElementById('cd-dias').textContent  = pad(Math.floor(diff / 86400000));
        document.getElementById('cd-horas').textContent = pad(Math.floor((diff % 86400000) / 3600000));
        document.getElementById('cd-min').textContent   = pad(Math.floor((diff % 3600000)  / 60000));
        document.getElementById('cd-seg').textContent   = pad(Math.floor((diff % 60000)    / 1000));
        setTimeout(actualizar, 1000);
    }
    actualizar();
})();
