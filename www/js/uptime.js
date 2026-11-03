/**
 * Actualiza el contador de uptime cada segundo.
 * El elemento debe tener id="uptime-display" y atributo data-segundos.
 */
(function() {
    var el = document.getElementById('uptime-display');
    if (!el) return;

    var segundos = parseInt(el.getAttribute('data-segundos'), 10);
    if (isNaN(segundos) || segundos < 0) return;

    /**
     * Formatea los segundos en un string legible (d, h, m, s).
     * @param {number} s - Segundos totales
     * @returns {string} Tiempo formateado
     */
    function formatear(s) {
        var d = Math.floor(s / 86400);
        var h = Math.floor((s % 86400) / 3600);
        var m = Math.floor((s % 3600) / 60);
        var sec = s % 60;
        if (d > 0) return d + 'd ' + h + 'h ' + m + 'm';
        if (h > 0) return h + 'h ' + m + 'm';
        if (m > 0) return m + 'm ' + sec + 's';
        return sec + 's';
    }

    setInterval(function() {
        segundos++;
        el.textContent = '⏱️ ' + formatear(segundos);
    }, 1000);
})();