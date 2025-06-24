<?php
// ========================================
// ADMIN/LOGOUT.PHP - CERRAR SESIÓN
// ========================================
// Este archivo maneja el cierre seguro de sesión del usuario
// Limpia todas las variables de sesión y destruye la sesión

// Iniciar sesión PHP para acceder a las variables de sesión
session_start();

// ========================================
// LIMPIEZA DE VARIABLES DE SESIÓN
// ========================================
// Vaciar completamente el array de sesión
// Esto elimina todas las variables de sesión del usuario
$_SESSION = array();

// ========================================
// DESTRUCCIÓN DE LA COOKIE DE SESIÓN
// ========================================
// Verificar si el navegador está configurado para usar cookies de sesión
if (ini_get("session.use_cookies")) {
    // Obtener los parámetros de la cookie de sesión actual
    $params = session_get_cookie_params();
    
    // Establecer la cookie de sesión con tiempo expirado (en el pasado)
    // Esto fuerza al navegador a eliminar la cookie
    setcookie(session_name(), '', time() - 42000,
        $params["path"],     // Ruta de la cookie
        $params["domain"],   // Dominio de la cookie
        $params["secure"],   // Si debe ser HTTPS
        $params["httponly"]  // Si debe ser solo HTTP
    );
}

// ========================================
// DESTRUCCIÓN DE LA SESIÓN
// ========================================
// Destruir completamente la sesión del servidor
// Esto elimina todos los datos de sesión almacenados
session_destroy();

// ========================================
// REDIRECCIÓN AL SITIO PÚBLICO
// ========================================
// Redirigir al usuario al sitio público principal
// con un mensaje de confirmación de cierre de sesión
header("Location: ../index.php");
exit(); // Terminar la ejecución del script inmediatamente
?> 