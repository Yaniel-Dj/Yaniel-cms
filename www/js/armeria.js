document.addEventListener("DOMContentLoaded", function() {
    
    // 1. Efecto de aparición suave para la armería al cargar
    const characterView = document.getElementById('character_view');
    if (characterView) {
        characterView.style.opacity = '0';
        characterView.style.transition = 'opacity 0.5s ease-in-out';
        
        setTimeout(() => {
            characterView.style.opacity = '1';
        }, 100);
    }

    // 2. Mejorar el selector de personajes (Buscador dentro del <select>)
    // Esto es útil si tienes más de 100 personajes y quieres buscar por texto
    const selectPersonaje = document.querySelector('select[name="guid"]');
    if (selectPersonaje) {
        // Si usas alguna librería como Select2, aquí se inicializaría:
        // $(selectPersonaje).select2();
        
        // Efecto visual al cambiar de personaje
        selectPersonaje.addEventListener('change', function() {
            if (this.value !== '') {
                document.body.style.cursor = 'wait';
            }
        });
    }

    // 3. Arreglar el problema de los tooltips en los bordes de la pantalla (Móviles)
    const tooltips = document.querySelectorAll('.tooltip');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const rect = this.getBoundingClientRect();
            // Si el tooltip se sale de la pantalla por la izquierda
            if (rect.left < 0) {
                this.style.left = '0';
                this.style.transform = 'translateX(0)';
            }
            // Si se sale por la derecha
            if (rect.right > window.innerWidth) {
                this.style.left = 'auto';
                this.style.right = '0';
                this.style.transform = 'translateX(0)';
            }
        });
    });
});
