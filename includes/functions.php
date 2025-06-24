<?php
// ========================================
// FUNCTIONS.PHP - FUNCIONES AUXILIARES
// ========================================
// Este archivo contiene todas las funciones auxiliares utilizadas
// en todo el proyecto para sanitización, autenticación, redirección, etc.

/**
 * SANITIZA DATOS DE ENTRADA
 * =========================
 * Limpia y sanitiza los datos que vienen del usuario para prevenir
 * ataques XSS y otros problemas de seguridad
 * 
 * @param mixed $data - Los datos a sanitizar (string, array, etc.)
 * @return mixed - Los datos sanitizados
 */
function sanitizeInput($data) {
    // Si es un array, sanitizar cada elemento recursivamente
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    // Limpiar espacios en blanco al inicio y final
    $data = trim($data);
    
    // Eliminar barras invertidas (backslashes)
    $data = stripslashes($data);
    
    // Convertir caracteres especiales a entidades HTML para prevenir XSS
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}

/**
 * VERIFICA SI EL USUARIO ESTÁ LOGUEADO
 * ====================================
 * Comprueba si existe una sesión activa de usuario
 * 
 * @return bool - true si está logueado, false si no
 */
function isLoggedIn() {
    // Verificar si existe la variable de sesión user_id y no está vacía
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * OBTIENE INFORMACIÓN DEL USUARIO ACTUAL
 * ======================================
 * Consulta la base de datos para obtener los datos del usuario logueado
 * 
 * @return array|null - Array con datos del usuario o null si no está logueado
 */
function getCurrentUser() {
    // Si no está logueado, retornar null
    if (!isLoggedIn()) return null;
    
    // Incluir archivo de conexión a la base de datos
    require_once __DIR__ . '/../config/database.php';
    
    try {
        $db = getDB(); // Obtener conexión a la base de datos
        
        // Consulta SQL: obtener datos del usuario por su ID
        $stmt = $db->prepare('SELECT id, usuario, nombre, email FROM usuarios WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]); // Ejecutar con el ID de la sesión
        
        return $stmt->fetch(); // Retornar los datos del usuario
    } catch (Exception $e) {
        // Si hay error, retornar null
        return null;
    }
}

/**
 * REDIRIGE A UNA URL CON MENSAJE OPCIONAL
 * =======================================
 * Redirige al usuario a otra página y opcionalmente muestra un mensaje
 * 
 * @param string $url - La URL a la que redirigir
 * @param string|null $message - Mensaje opcional a mostrar
 * @param string $type - Tipo de mensaje (success, error, warning, info)
 */
function redirect($url, $message = null, $type = 'info') {
    // Si hay mensaje, guardarlo en la sesión para mostrarlo después
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    
    // Redirigir al usuario
    header('Location: ' . $url);
    exit(); // Terminar la ejecución del script
}

/**
 * MUESTRA MENSAJES FLASH
 * ======================
 * Muestra mensajes que se guardaron en la sesión (después de redirecciones)
 * y los elimina para que no se muestren de nuevo
 */
function showFlashMessage() {
    // Verificar si existe un mensaje flash en la sesión
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info'; // Tipo por defecto
        
        // Mapear tipos de mensaje a clases CSS de Bootstrap
        $alertClass = [
            'success' => 'alert-success',  // Verde
            'error' => 'alert-danger',     // Rojo
            'warning' => 'alert-warning',  // Amarillo
            'info' => 'alert-info'         // Azul
        ][$type] ?? 'alert-info'; // Si no existe el tipo, usar info
        
        // Generar el HTML del mensaje con Bootstrap
        echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>"
            . $message .
            "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        
        // Eliminar el mensaje de la sesión para que no se muestre de nuevo
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    }
}

/**
 * FORMATEA FECHAS
 * ===============
 * Convierte una fecha de la base de datos a un formato legible
 * 
 * @param string $date - Fecha en formato de base de datos
 * @param string $format - Formato deseado (por defecto: d/m/Y H:i)
 * @return string - Fecha formateada
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    // Convertir la fecha de la base de datos al formato especificado
    return date($format, strtotime($date));
}

/**
 * TRUNCA TEXTO
 * ============
 * Corta un texto largo y agrega puntos suspensivos si es necesario
 * 
 * @param string $text - Texto a truncar
 * @param int $length - Longitud máxima (por defecto: 100)
 * @param string $suffix - Sufijo a agregar (por defecto: '...')
 * @return string - Texto truncado
 */
function truncateText($text, $length = 100, $suffix = '...') {
    // Si el texto es más corto que la longitud máxima, retornarlo tal como está
    if (strlen($text) <= $length) return $text;
    
    // Cortar el texto a la longitud especificada y agregar el sufijo
    return substr($text, 0, $length) . $suffix;
} 