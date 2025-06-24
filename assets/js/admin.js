/**
 * ========================================
 * ASSETS/JS/ADMIN.JS - JAVASCRIPT DEL PANEL DE ADMINISTRACIÓN
 * ========================================
 * Este archivo contiene todas las funcionalidades JavaScript
 * para el panel de administración del portafolio
 */

// ========================================
// CONFIGURACIÓN INICIAL
// ========================================
// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function () {
    // Inicializar todas las funcionalidades del admin
    initializeAdminFeatures();
});

// ========================================
// FUNCIÓN PRINCIPAL DE INICIALIZACIÓN
// ========================================
/**
 * INICIALIZA TODAS LAS FUNCIONALIDADES DEL PANEL ADMIN
 * ===================================================
 * Configura todos los event listeners y funcionalidades
 */
function initializeAdminFeatures() {
    // Inicializar confirmaciones de eliminación
    initializeDeleteConfirmations();

    // Inicializar validaciones de formularios
    initializeFormValidations();

    // Inicializar funcionalidades de imágenes
    initializeImageFeatures();

    // Inicializar funcionalidades de navegación
    initializeNavigationFeatures();

    // Inicializar funcionalidades de tabla
    initializeTableFeatures();
}

// ========================================
// FUNCIONALIDADES DE CONFIRMACIÓN DE ELIMINACIÓN
// ========================================
/**
 * INICIALIZA LAS CONFIRMACIONES DE ELIMINACIÓN
 * ===========================================
 * Agrega confirmaciones antes de eliminar elementos
 */
function initializeDeleteConfirmations() {
    // Buscar todos los botones de eliminar
    const deleteButtons = document.querySelectorAll('[data-action="delete"]');

    // Agregar event listener a cada botón
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault(); // Prevenir acción por defecto

            // Obtener información del elemento a eliminar
            const itemName = this.getAttribute('data-item-name') || 'este elemento';
            const itemId = this.getAttribute('data-item-id');

            // Mostrar diálogo de confirmación
            if (confirm(`¿Estás seguro de que deseas eliminar ${itemName}? Esta acción no se puede deshacer.`)) {
                // Si confirma, proceder con la eliminación
                proceedWithDeletion(itemId, this.getAttribute('data-delete-url'));
            }
        });
    });
}

/**
 * PROCEDE CON LA ELIMINACIÓN DEL ELEMENTO
 * ======================================
 * Ejecuta la eliminación después de la confirmación
 * 
 * @param {string} itemId - ID del elemento a eliminar
 * @param {string} deleteUrl - URL para la eliminación
 */
function proceedWithDeletion(itemId, deleteUrl) {
    // Construir la URL de eliminación
    const url = deleteUrl + (itemId ? `?id=${itemId}` : '');

    // Redirigir a la URL de eliminación
    window.location.href = url;
}

// ========================================
// FUNCIONALIDADES DE VALIDACIÓN DE FORMULARIOS
// ========================================
/**
 * INICIALIZA LAS VALIDACIONES DE FORMULARIOS
 * ==========================================
 * Agrega validaciones en tiempo real a los formularios
 */
function initializeFormValidations() {
    // Buscar todos los formularios
    const forms = document.querySelectorAll('form[data-validate="true"]');

    // Agregar validaciones a cada formulario
    forms.forEach(form => {
        // Validar al enviar el formulario
        form.addEventListener('submit', function (e) {
            if (!validateForm(this)) {
                e.preventDefault(); // Prevenir envío si hay errores
            }
        });

        // Validar en tiempo real
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function () {
                validateField(this);
            });

            input.addEventListener('input', function () {
                clearFieldError(this);
            });
        });
    });
}

/**
 * VALIDA UN FORMULARIO COMPLETO
 * =============================
 * Verifica que todos los campos requeridos estén completos
 * 
 * @param {HTMLFormElement} form - Formulario a validar
 * @returns {boolean} - True si el formulario es válido
 */
function validateForm(form) {
    let isValid = true;

    // Buscar todos los campos requeridos
    const requiredFields = form.querySelectorAll('[required]');

    // Validar cada campo requerido
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });

    return isValid;
}

/**
 * VALIDA UN CAMPO INDIVIDUAL
 * ==========================
 * Verifica que un campo específico sea válido
 * 
 * @param {HTMLElement} field - Campo a validar
 * @returns {boolean} - True si el campo es válido
 */
function validateField(field) {
    const value = field.value.trim();
    const isRequired = field.hasAttribute('required');
    const maxLength = field.getAttribute('maxlength');

    // Limpiar errores previos
    clearFieldError(field);

    // Validar campo requerido
    if (isRequired && value === '') {
        showFieldError(field, 'Este campo es obligatorio');
        return false;
    }

    // Validar longitud máxima
    if (maxLength && value.length > parseInt(maxLength)) {
        showFieldError(field, `Máximo ${maxLength} caracteres`);
        return false;
    }

    // Validar tipo de campo específico
    if (field.type === 'email' && value !== '') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Formato de email inválido');
            return false;
        }
    }

    if (field.type === 'url' && value !== '') {
        try {
            new URL(value);
        } catch {
            showFieldError(field, 'URL inválida');
            return false;
        }
    }

    return true;
}

/**
 * MUESTRA UN ERROR EN UN CAMPO
 * ============================
 * Agrega un mensaje de error visual al campo
 * 
 * @param {HTMLElement} field - Campo donde mostrar el error
 * @param {string} message - Mensaje de error
 */
function showFieldError(field, message) {
    // Agregar clase de error al campo
    field.classList.add('is-invalid');

    // Crear elemento de error
    const errorElement = document.createElement('div');
    errorElement.className = 'invalid-feedback';
    errorElement.textContent = message;

    // Insertar después del campo
    field.parentNode.appendChild(errorElement);
}

/**
 * LIMPIA EL ERROR DE UN CAMPO
 * ===========================
 * Remueve el mensaje de error del campo
 * 
 * @param {HTMLElement} field - Campo a limpiar
 */
function clearFieldError(field) {
    // Remover clase de error
    field.classList.remove('is-invalid');

    // Remover mensaje de error
    const errorElement = field.parentNode.querySelector('.invalid-feedback');
    if (errorElement) {
        errorElement.remove();
    }
}

// ========================================
// FUNCIONALIDADES DE IMÁGENES
// ========================================
/**
 * INICIALIZA LAS FUNCIONALIDADES DE IMÁGENES
 * ==========================================
 * Configura previsualización y validación de imágenes
 */
function initializeImageFeatures() {
    // Buscar inputs de archivo de imagen
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');

    // Agregar funcionalidades a cada input
    imageInputs.forEach(input => {
        input.addEventListener('change', function (e) {
            handleImageSelection(this, e);
        });
    });
}

/**
 * MANEJA LA SELECCIÓN DE UNA IMAGEN
 * =================================
 * Valida y previsualiza la imagen seleccionada
 * 
 * @param {HTMLInputElement} input - Input de archivo
 * @param {Event} event - Evento de cambio
 */
function handleImageSelection(input, event) {
    const file = event.target.files[0];

    if (!file) {
        return;
    }

    // Validar tipo de archivo
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        alert('Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG, GIF y WEBP.');
        input.value = '';
        return;
    }

    // Validar tamaño (5MB máximo)
    const maxSize = 5 * 1024 * 1024; // 5MB en bytes
    if (file.size > maxSize) {
        alert('La imagen es demasiado grande. Máximo 5MB.');
        input.value = '';
        return;
    }

    // Previsualizar imagen
    previewImage(file, input);
}

/**
 * PREVISUALIZA UNA IMAGEN SELECCIONADA
 * ====================================
 * Muestra una vista previa de la imagen antes de subirla
 * 
 * @param {File} file - Archivo de imagen
 * @param {HTMLInputElement} input - Input de archivo
 */
function previewImage(file, input) {
    const reader = new FileReader();

    reader.onload = function (e) {
        // Buscar contenedor de previsualización
        let previewContainer = input.parentNode.querySelector('.image-preview');

        // Crear contenedor si no existe
        if (!previewContainer) {
            previewContainer = document.createElement('div');
            previewContainer.className = 'image-preview mt-2';
            input.parentNode.appendChild(previewContainer);
        }

        // Crear elemento de imagen
        const img = document.createElement('img');
        img.src = e.target.result;
        img.style.maxWidth = '200px';
        img.style.maxHeight = '150px';
        img.style.borderRadius = '4px';
        img.style.border = '1px solid #ddd';

        // Limpiar contenedor y agregar imagen
        previewContainer.innerHTML = '';
        previewContainer.appendChild(img);
    };

    reader.readAsDataURL(file);
}

// ========================================
// FUNCIONALIDADES DE NAVEGACIÓN
// ========================================
/**
 * INICIALIZA LAS FUNCIONALIDADES DE NAVEGACIÓN
 * ============================================
 * Configura navegación responsive y efectos
 */
function initializeNavigationFeatures() {
    // Configurar navegación responsive
    setupResponsiveNavigation();

    // Configurar efectos de hover
    setupHoverEffects();
}

/**
 * CONFIGURA LA NAVEGACIÓN RESPONSIVE
 * ==================================
 * Maneja el comportamiento de la navegación en dispositivos móviles
 */
function setupResponsiveNavigation() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');

    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function () {
            navbarCollapse.classList.toggle('show');
        });
    }
}

/**
 * CONFIGURA EFECTOS DE HOVER
 * ==========================
 * Agrega efectos visuales al hacer hover sobre elementos
 */
function setupHoverEffects() {
    // Efectos en tarjetas
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-2px)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
        });
    });
}

// ========================================
// FUNCIONALIDADES DE TABLA
// ========================================
/**
 * INICIALIZA LAS FUNCIONALIDADES DE TABLA
 * =======================================
 * Configura ordenamiento, filtrado y paginación
 */
function initializeTableFeatures() {
    // Configurar ordenamiento de columnas
    setupTableSorting();

    // Configurar filtrado
    setupTableFiltering();
}

/**
 * CONFIGURA EL ORDENAMIENTO DE TABLAS
 * ===================================
 * Permite ordenar las tablas haciendo clic en los encabezados
 */
function setupTableSorting() {
    const sortableHeaders = document.querySelectorAll('th[data-sortable="true"]');

    sortableHeaders.forEach(header => {
        header.addEventListener('click', function () {
            const table = this.closest('table');
            const columnIndex = Array.from(this.parentNode.children).indexOf(this);
            const isAscending = this.classList.contains('sort-asc');

            // Limpiar clases de ordenamiento previas
            sortableHeaders.forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });

            // Agregar clase de ordenamiento actual
            this.classList.add(isAscending ? 'sort-desc' : 'sort-asc');

            // Ordenar tabla
            sortTable(table, columnIndex, !isAscending);
        });
    });
}

/**
 * ORDENA UNA TABLA POR COLUMNA
 * ============================
 * Ordena los datos de la tabla según la columna especificada
 * 
 * @param {HTMLTableElement} table - Tabla a ordenar
 * @param {number} columnIndex - Índice de la columna
 * @param {boolean} ascending - True para orden ascendente
 */
function sortTable(table, columnIndex, ascending) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    // Ordenar filas
    rows.sort((a, b) => {
        const aValue = a.children[columnIndex].textContent.trim();
        const bValue = b.children[columnIndex].textContent.trim();

        if (ascending) {
            return aValue.localeCompare(bValue);
        } else {
            return bValue.localeCompare(aValue);
        }
    });

    // Reinsertar filas ordenadas
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * CONFIGURA EL FILTRADO DE TABLAS
 * ===============================
 * Permite filtrar los datos de las tablas
 */
function setupTableFiltering() {
    const filterInputs = document.querySelectorAll('.table-filter');

    filterInputs.forEach(input => {
        input.addEventListener('input', function () {
            const tableId = this.getAttribute('data-table');
            const table = document.getElementById(tableId);
            const filterValue = this.value.toLowerCase();

            if (table) {
                filterTable(table, filterValue);
            }
        });
    });
}

/**
 * FILTRA UNA TABLA POR TEXTO
 * ==========================
 * Muestra solo las filas que contengan el texto especificado
 * 
 * @param {HTMLTableElement} table - Tabla a filtrar
 * @param {string} filterValue - Texto para filtrar
 */
function filterTable(table, filterValue) {
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(filterValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// ========================================
// FUNCIONES DE UTILIDAD
// ========================================
/**
 * MUESTRA UN MENSAJE DE NOTIFICACIÓN
 * ===================================
 * Muestra una notificación temporal al usuario
 * 
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo de notificación (success, error, warning, info)
 * @param {number} duration - Duración en milisegundos
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} notification`;
    notification.textContent = message;

    // Estilos para la notificación
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';

    // Agregar al DOM
    document.body.appendChild(notification);

    // Remover después del tiempo especificado
    setTimeout(() => {
        notification.remove();
    }, duration);
}

/**
 * FORMATEA UNA FECHA PARA MOSTRAR
 * ================================
 * Convierte una fecha a formato legible
 * 
 * @param {string} dateString - Fecha en formato string
 * @returns {string} - Fecha formateada
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * VALIDA UNA URL
 * ==============
 * Verifica si una URL es válida
 * 
 * @param {string} url - URL a validar
 * @returns {boolean} - True si la URL es válida
 */
function isValidUrl(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
} 