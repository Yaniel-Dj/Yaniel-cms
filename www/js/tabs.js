/**
 * Cambia entre pestañas (tabs) y muestra el contenido correspondiente.
 * @param {string} tab - Nombre de la pestaña (ej: 'login', 'registro')
 * @param {HTMLElement} el - Elemento que se ha clickeado (la pestaña)
 */
function cambiarTab(tab, el) {
    // Quitar 'activo' de todas las pestañas y contenidos
    document.querySelectorAll('.tab').forEach(function(t) {
        t.classList.remove('activo');
    });
    document.querySelectorAll('.tab-content').forEach(function(c) {
        c.classList.remove('activo');
    });

    // Añadir 'activo' a la pestaña clickeada
    if (el) {
        el.classList.add('activo');
    }

    // Añadir 'activo' al contenido correspondiente (si existe)
    var contenido = document.getElementById('tab-' + tab);
    if (contenido) {
        contenido.classList.add('activo');
    }
}

/**
 * Muestra una sección y oculta las demás.
 * @param {string} sec - Nombre de la sección (ej: 'tienda', 'noticias', 'gm')
 */
function mostrarSeccion(sec) {
    ['tienda', 'noticias', 'gm'].forEach(function(s) {
        var e = document.getElementById('seccion-' + s);
        if (e) {
            e.style.display = (s === sec) ? 'block' : 'none';
        }
    });
}