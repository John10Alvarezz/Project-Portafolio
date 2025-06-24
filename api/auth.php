<?php
// ========================================
// API/AUTH.PHP - ENDPOINT DE AUTENTICACIÓN
// ========================================
// Este archivo maneja la autenticación de usuarios para la API
// Incluye login, logout y verificación de tokens

// Incluir archivos necesarios para la funcionalidad
require_once 'config.php';           // Configuración de la API
require_once '../config/database.php'; // Conexión a la base de datos
require_once '../includes/functions.php'; // Funciones auxiliares

// ========================================
// OBTENCIÓN DE LA RUTA DE LA PETICIÓN
// ========================================
// Obtener la ruta de la petición para determinar qué endpoint se solicita
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathSegments = explode('/', trim($path, '/'));

// Obtener el último segmento de la ruta (el endpoint específico)
$endpoint = end($pathSegments);

// ========================================
// RUTEO DE ENDPOINTS
// ========================================
// Determinar qué acción realizar basado en el endpoint y método HTTP
switch ($endpoint) {
    case 'login':
        // Endpoint para iniciar sesión
        handleLogin();
        break;
        
    case 'logout':
        // Endpoint para cerrar sesión
        handleLogout();
        break;
        
    case 'verify':
        // Endpoint para verificar token
        handleVerifyToken();
        break;
        
    default:
        // Endpoint no encontrado
        sendErrorResponse('Endpoint no encontrado', 404);
        break;
}

// ========================================
// FUNCIÓN PARA MANEJAR LOGIN
// ========================================
/**
 * MANEJA EL PROCESO DE INICIO DE SESIÓN
 * =====================================
 * Valida credenciales y genera token de acceso
 */
function handleLogin() {
    // Verificar que el método sea POST
    validateHTTPMethod('POST');
    
    // Obtener datos del body de la petición
    $data = getRequestBody();
    
    // ========================================
    // VALIDACIÓN DE DATOS REQUERIDOS
    // ========================================
    // Verificar que se proporcionen usuario y contraseña
    if (!isset($data['usuario']) || !isset($data['password'])) {
        sendErrorResponse('Usuario y contraseña son requeridos', 400);
    }
    
    $usuario = trim($data['usuario']); // Usuario o email
    $password = $data['password'];     // Contraseña
    
    // Validar que los campos no estén vacíos
    if (empty($usuario) || empty($password)) {
        sendErrorResponse('Usuario y contraseña no pueden estar vacíos', 400);
    }
    
    // ========================================
    // VERIFICACIÓN DE CREDENCIALES
    // ========================================
    try {
        $db = getDB(); // Obtener conexión a la base de datos
        
        // Consulta SQL: buscar usuario por nombre de usuario O email
        $stmt = $db->prepare('SELECT id, usuario, password, nombre, email FROM usuarios WHERE usuario = ? OR email = ?');
        $stmt->execute([$usuario, $usuario]); // Ejecutar con el mismo valor para ambos campos
        
        $user = $stmt->fetch(); // Obtener resultado
        
        // Verificar si el usuario existe y la contraseña es correcta
        if (!$user || !password_verify($password, $user['password'])) {
            sendErrorResponse('Credenciales inválidas', 401);
        }
        
        // ========================================
        // GENERACIÓN DE TOKEN DE ACCESO
        // ========================================
        // Generar token único para la sesión
        $token = bin2hex(random_bytes(32)); // Token de 64 caracteres hexadecimales
        
        // Calcular fecha de expiración (24 horas desde ahora)
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // ========================================
        // GUARDADO DE SESIÓN EN BASE DE DATOS
        // ========================================
        // Preparar consulta SQL para insertar la sesión
        $stmt = $db->prepare('INSERT INTO sesiones (usuario_id, token, expires_at) VALUES (?, ?, ?)');
        $stmt->execute([$user['id'], $token, $expiresAt]); // Ejecutar con datos de la sesión
        
        // ========================================
        // RESPUESTA DE ÉXITO
        // ========================================
        // Preparar datos de respuesta (sin información sensible)
        $responseData = [
            'token' => $token,
            'expires_at' => $expiresAt,
            'user' => [
                'id' => $user['id'],
                'usuario' => $user['usuario'],
                'nombre' => $user['nombre'],
                'email' => $user['email']
            ]
        ];
        
        // Enviar respuesta exitosa
        sendSuccessResponse($responseData, 'Login exitoso');
        
    } catch (Exception $e) {
        // Error en la base de datos
        sendErrorResponse('Error interno del servidor', 500);
    }
}

// ========================================
// FUNCIÓN PARA MANEJAR LOGOUT
// ========================================
/**
 * MANEJA EL PROCESO DE CIERRE DE SESIÓN
 * =====================================
 * Invalida el token de acceso
 */
function handleLogout() {
    // Verificar que el método sea POST
    validateHTTPMethod('POST');
    
    // ========================================
    // OBTENCIÓN Y VALIDACIÓN DEL TOKEN
    // ========================================
    // Obtener el token del header Authorization
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    // Verificar formato del header Authorization
    if (!preg_match('/^Bearer\s+(.*)$/', $authHeader, $matches)) {
        sendErrorResponse('Token de autorización requerido', 401);
    }
    
    $token = $matches[1]; // Extraer el token del header
    
    // ========================================
    // ELIMINACIÓN DE LA SESIÓN
    // ========================================
    try {
        $db = getDB(); // Obtener conexión a la base de datos
        
        // Preparar consulta SQL para eliminar la sesión
        $stmt = $db->prepare('DELETE FROM sesiones WHERE token = ?');
        $stmt->execute([$token]); // Ejecutar con el token
        
        // Enviar respuesta exitosa
        sendSuccessResponse(null, 'Logout exitoso');
        
    } catch (Exception $e) {
        // Error en la base de datos
        sendErrorResponse('Error interno del servidor', 500);
    }
}

// ========================================
// FUNCIÓN PARA VERIFICAR TOKEN
// ========================================
/**
 * MANEJA LA VERIFICACIÓN DE TOKEN
 * ===============================
 * Verifica si un token es válido y no ha expirado
 */
function handleVerifyToken() {
    // Verificar que el método sea GET
    validateHTTPMethod('GET');
    
    // ========================================
    // OBTENCIÓN Y VALIDACIÓN DEL TOKEN
    // ========================================
    // Obtener el token del header Authorization
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    // Verificar formato del header Authorization
    if (!preg_match('/^Bearer\s+(.*)$/', $authHeader, $matches)) {
        sendErrorResponse('Token de autorización requerido', 401);
    }
    
    $token = $matches[1]; // Extraer el token del header
    
    // ========================================
    // VERIFICACIÓN DE VALIDEZ DEL TOKEN
    // ========================================
    try {
        $db = getDB(); // Obtener conexión a la base de datos
        
        // Consulta SQL: verificar token y obtener información del usuario
        $stmt = $db->prepare('
            SELECT s.token, s.expires_at, u.id, u.usuario, u.nombre, u.email 
            FROM sesiones s 
            JOIN usuarios u ON s.usuario_id = u.id 
            WHERE s.token = ? AND s.expires_at > NOW()
        ');
        $stmt->execute([$token]); // Ejecutar con el token
        
        $session = $stmt->fetch(); // Obtener resultado
        
        // Verificar si el token es válido y no ha expirado
        if (!$session) {
            sendErrorResponse('Token inválido o expirado', 401);
        }
        
        // ========================================
        // RESPUESTA DE VERIFICACIÓN EXITOSA
        // ========================================
        // Preparar datos de respuesta
        $responseData = [
            'valid' => true,
            'expires_at' => $session['expires_at'],
            'user' => [
                'id' => $session['id'],
                'usuario' => $session['usuario'],
                'nombre' => $session['nombre'],
                'email' => $session['email']
            ]
        ];
        
        // Enviar respuesta exitosa
        sendSuccessResponse($responseData, 'Token válido');
        
    } catch (Exception $e) {
        // Error en la base de datos
        sendErrorResponse('Error interno del servidor', 500);
    }
}

// ========================================
// FUNCIÓN AUXILIAR PARA OBTENER USUARIO ACTUAL
// ========================================
/**
 * FUNCIÓN AUXILIAR PARA OBTENER USUARIO ACTUAL
 * ============================================
 * Verifica el token y retorna la información del usuario
 * Utilizada por otros endpoints protegidos
 * 
 * @return array - Información del usuario autenticado
 */
function getCurrentUserFromToken() {
    // Obtener el token del header Authorization
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    // Verificar formato del header Authorization
    if (!preg_match('/^Bearer\s+(.*)$/', $authHeader, $matches)) {
        sendErrorResponse('Token de autorización requerido', 401);
    }
    
    $token = $matches[1]; // Extraer el token del header
    
    try {
        $db = getDB(); // Obtener conexión a la base de datos
        
        // Consulta SQL: verificar token y obtener información del usuario
        $stmt = $db->prepare('
            SELECT u.id, u.usuario, u.nombre, u.email 
            FROM sesiones s 
            JOIN usuarios u ON s.usuario_id = u.id 
            WHERE s.token = ? AND s.expires_at > NOW()
        ');
        $stmt->execute([$token]); // Ejecutar con el token
        
        $user = $stmt->fetch(); // Obtener resultado
        
        // Verificar si el token es válido
        if (!$user) {
            sendErrorResponse('Token inválido o expirado', 401);
        }
        
        return $user; // Retornar información del usuario
        
    } catch (Exception $e) {
        // Error en la base de datos
        sendErrorResponse('Error interno del servidor', 500);
    }
}
?> 