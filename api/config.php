<?php
// ========================================
// API/CONFIG.PHP - CONFIGURACIÓN DE LA API
// ========================================
// Este archivo contiene la configuración básica para la API REST
// Incluye headers CORS, configuración de respuesta JSON y manejo de errores

// ========================================
// CONFIGURACIÓN DE CORS (CROSS-ORIGIN RESOURCE SHARING)
// ========================================
// Permitir acceso desde cualquier origen (en producción deberías especificar dominios)
header('Access-Control-Allow-Origin: *');

// Permitir métodos HTTP específicos
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

// Permitir headers específicos
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// ========================================
// CONFIGURACIÓN DE HEADERS DE RESPUESTA
// ========================================
// Establecer el tipo de contenido como JSON
header('Content-Type: application/json; charset=utf-8');

// Configurar cache para evitar almacenamiento en caché de respuestas API
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// ========================================
// MANEJO DE MÉTODO OPTIONS (PREFLIGHT)
// ========================================
// Responder inmediatamente a las solicitudes OPTIONS (preflight de CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Código de respuesta exitosa
    exit(); // Terminar la ejecución
}

// ========================================
// CONFIGURACIÓN DE MANEJO DE ERRORES
// ========================================
// Configurar el manejo de errores para la API
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en producción

// ========================================
// FUNCIÓN PARA ENVIAR RESPUESTAS JSON
// ========================================
/**
 * FUNCIÓN PARA ENVIAR RESPUESTAS JSON ESTANDARIZADAS
 * ==================================================
 * Envía una respuesta JSON con estructura consistente
 * 
 * @param mixed $data - Datos a enviar en la respuesta
 * @param int $statusCode - Código de estado HTTP (por defecto 200)
 * @param string $message - Mensaje descriptivo (opcional)
 * @param bool $success - Indica si la operación fue exitosa (por defecto true)
 */
function sendJSONResponse($data = null, $statusCode = 200, $message = '', $success = true) {
    // Establecer el código de estado HTTP
    http_response_code($statusCode);
    
    // Construir la estructura de respuesta estándar
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Enviar la respuesta JSON
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit(); // Terminar la ejecución
}

// ========================================
// FUNCIÓN PARA ENVIAR RESPUESTAS DE ERROR
// ========================================
/**
 * FUNCIÓN PARA ENVIAR RESPUESTAS DE ERROR
 * =======================================
 * Envía una respuesta JSON de error estandarizada
 * 
 * @param string $message - Mensaje de error
 * @param int $statusCode - Código de estado HTTP (por defecto 400)
 * @param mixed $data - Datos adicionales del error (opcional)
 */
function sendErrorResponse($message, $statusCode = 400, $data = null) {
    sendJSONResponse($data, $statusCode, $message, false);
}

// ========================================
// FUNCIÓN PARA ENVIAR RESPUESTAS DE ÉXITO
// ========================================
/**
 * FUNCIÓN PARA ENVIAR RESPUESTAS DE ÉXITO
 * =======================================
 * Envía una respuesta JSON de éxito estandarizada
 * 
 * @param mixed $data - Datos de la respuesta
 * @param string $message - Mensaje de éxito (opcional)
 * @param int $statusCode - Código de estado HTTP (por defecto 200)
 */
function sendSuccessResponse($data, $message = '', $statusCode = 200) {
    sendJSONResponse($data, $statusCode, $message, true);
}

// ========================================
// FUNCIÓN PARA VALIDAR MÉTODO HTTP
// ========================================
/**
 * FUNCIÓN PARA VALIDAR MÉTODO HTTP
 * =================================
 * Verifica que el método HTTP sea el esperado
 * 
 * @param string $expectedMethod - Método HTTP esperado
 * @return void - Termina la ejecución si el método no coincide
 */
function validateHTTPMethod($expectedMethod) {
    if ($_SERVER['REQUEST_METHOD'] !== $expectedMethod) {
        sendErrorResponse('Método no permitido', 405);
    }
}

// ========================================
// FUNCIÓN PARA OBTENER DATOS DEL BODY
// ========================================
/**
 * FUNCIÓN PARA OBTENER DATOS DEL BODY
 * ===================================
 * Obtiene y decodifica los datos JSON del body de la petición
 * 
 * @return array - Datos decodificados del body
 */
function getRequestBody() {
    // Obtener el contenido del body de la petición
    $input = file_get_contents('php://input');
    
    // Decodificar JSON a array
    $data = json_decode($input, true);
    
    // Si hay error en la decodificación, enviar error
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendErrorResponse('JSON inválido en el body de la petición', 400);
    }
    
    return $data;
}

require_once __DIR__ . '/../config/database.php';

function sendResponse($data, $status = 200, $message = '') {
    http_response_code($status);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

function sendError($message, $status = 400) {
    sendResponse(null, $status, $message);
}

function getRequestData() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

function validateAuthToken() {
    $headers = getallheaders();
    $token = null;
    if (isset($headers['Authorization'])) {
        $auth = $headers['Authorization'];
        if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            $token = $matches[1];
        }
    }
    if (!$token) {
        sendError('Token de autenticación requerido', 401);
    }
    try {
        $db = getDB();
        $stmt = $db->prepare('SELECT u.id, u.usuario, u.nombre, u.email FROM sesiones s JOIN usuarios u ON s.usuario_id = u.id WHERE s.token = ? AND s.expires_at > NOW()');
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        if (!$user) {
            sendError('Token inválido o expirado', 401);
        }
        return $user;
    } catch (Exception $e) {
        sendError('Error interno del servidor', 500);
    }
} 