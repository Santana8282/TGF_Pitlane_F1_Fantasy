function abrirModalNuevo() {
    document.getElementById('modal-titulo').textContent = 'Añadir Piloto';
    document.getElementById('modal-accion').value = 'nuevo_piloto';
    document.getElementById('modal-id').value = '';
    document.getElementById('modal-save-btn').textContent = '+ Añadir piloto';
    document.getElementById('modal-form').reset();
    document.getElementById('admin-modal-overlay').classList.add('activo');
}

function abrirModalEditar(p) {
    document.getElementById('modal-titulo').textContent = 'Editar Piloto';
    document.getElementById('modal-accion').value = 'guardar_edicion';
    document.getElementById('modal-id').value    = p.id_piloto;
    document.getElementById('modal-nombre').value = p.nombre;
    document.getElementById('modal-numero').value = p.numero;
    document.getElementById('modal-nac').value    = p.nacionalidad;
    document.getElementById('modal-esc').value    = p.id_escuderia;
    document.getElementById('modal-precio').value = p.precio;
    document.getElementById('modal-img').value    = p.imagen_url;
    document.getElementById('modal-save-btn').textContent = 'Guardar cambios';
    document.getElementById('admin-modal-overlay').classList.add('activo');
}

function cerrarModal() {
    document.getElementById('admin-modal-overlay').classList.remove('activo');
}

function confirmarEliminar(id, nombre, numero, escuderia, color) {
    document.getElementById('confirm-id').value        = id;
    document.getElementById('del-card-nombre').textContent = nombre;
    document.getElementById('del-card-num').textContent    = '#' + numero;
    document.getElementById('del-card-esc').textContent    = escuderia;
    document.getElementById('del-card-stripe').style.background = color;
    document.getElementById('del-card-num').style.color   = color;
    document.getElementById('del-header').style.background =
        'linear-gradient(160deg, #200808 0%, ' + color + '33 100%)';
    document.getElementById('admin-confirm-overlay').classList.add('activo');
}

function cerrarConfirm() {
    document.getElementById('admin-confirm-overlay').classList.remove('activo');
}

document.getElementById('admin-modal-overlay').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
document.getElementById('admin-confirm-overlay').addEventListener('click', function(e) {
    if (e.target === this) cerrarConfirm();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { cerrarModal(); cerrarConfirm(); }
});
