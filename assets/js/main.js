// ========================================
// MAIN.JS - JAVASCRIPT DEL SITIO PÚBLICO
// ========================================
// Este archivo contiene todas las funcionalidades JavaScript
// para animaciones, navegación y efectos interactivos

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function () {

    // ========================================
    // NAVEGACIÓN MÓVIL
    // ========================================
    // Cerrar el menú hamburguesa automáticamente cuando
    // se hace clic en un enlace (especialmente útil en móviles)

    // Obtener todos los enlaces de navegación
    var navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    // Obtener el contenedor del menú colapsable
    var navbarCollapse = document.getElementById('navbarNav');

    // Agregar evento de clic a cada enlace
    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            // Verificar si estamos en pantalla pequeña y el menú está abierto
            if (window.innerWidth < 992 && navbarCollapse.classList.contains('show')) {
                // Crear instancia de Bootstrap Collapse y cerrar el menú
                var bsCollapse = new bootstrap.Collapse(navbarCollapse, { toggle: false });
                bsCollapse.hide();
            }
        });
    });

    // ========================================
    // ANIMACIONES DE APARICIÓN
    // ========================================
    // Sistema de animaciones que hace que los elementos
    // aparezcan suavemente cuando entran en el viewport

    // Obtener todos los elementos que deben animarse
    var animatedElements = document.querySelectorAll('.project-card, #about');

    // Verificar si el navegador soporta Intersection Observer
    if ('IntersectionObserver' in window) {
        // Crear el observador de intersección
        var animationObserver = new IntersectionObserver(function (entries) {
            // Procesar cada entrada observada
            entries.forEach(function (entry) {
                // Si el elemento está visible en el viewport
                if (entry.isIntersecting) {
                    // Agregar la clase 'appeared' para activar la animación
                    entry.target.classList.add('appeared');
                }
            });
        }, {
            threshold: 0.1 // Activar cuando el 10% del elemento sea visible
        });

        // Observar cada elemento animable
        animatedElements.forEach(function (element) {
            animationObserver.observe(element);
        });
    }

    // Nota: Si el navegador no soporta Intersection Observer,
    // los elementos simplemente aparecerán sin animación
}); 