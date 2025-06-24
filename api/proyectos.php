<?php
// ========================================
// API/PROYECTOS.PHP - ENDPOINTS DE PROYECTOS
// ========================================
// Este archivo maneja todas las operaciones CRUD de proyectos
// Incluye listar, crear, actualizar y eliminar proyectos

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
    case 'list':
        // Endpoint para listar proyectos (público)
        handleListProjects();
        break;
        
    case 'create':
        // Endpoint para crear proyecto (protegido)
        handleCreateProject();
        break;
        
    case 'update':
        // Endpoint para actualizar proyecto (protegido)
        handleUpdateProject();
        break;
        
    case 'delete':
        // Endpoint para eliminar proyecto (protegido)
        handleDeleteProject();
        break;
        
    case 'get':
        // Endpoint para obtener proyecto específico (público)
        handleGetProject();
        break;
        
    default:
        // Endpoint no encontrado
        sendErrorResponse('Endpoint no encontrado', 404);
        break;
}

// ========================================
// FUNCIÓN PARA LISTAR PROYECTOS (PÚBLICO)
// ========================================
/**
 * MANEJA LA LISTA DE PROYECTOS PÚBLICOS
 * =====================================
 * Retorna todos los proyectos activos ordenados por fecha
 */
function handleListProjects() {
    // Verificar que el método sea GET
    validateHTTPMethod('GET');
    
    try {
        $db = getDB(); // Obtener conexión a la base de datos
        
        // Consulta SQL: obtener solo proyectos activos ordenados por fecha
        $stmt = $db->prepare('
            SELECT id, titulo, descripcion, tecnologias, url_github, url_produccion, 
                   imagen, estado, orden_mostrar, created_at, updated_at 
            FROM proyectos 
            WHERE estado = "activo" 
            ORDER BY orden_mostrar ASC, created_at DESC
        ');
        $stmt->execute(); // Ejecutar consulta
        
        $proyectos = $stmt->fetchAll(); // Obtener todos los resultados
        
        // ========================================
        // PROCESAMIENTO DE DATOS PARA RESPUESTA
        // ========================================
        // Procesar cada proyecto para incluir URLs completas de imágenes
        foreach ($proyectos as &$proyecto) {
            // Si hay imagen, construir la URL completa
            if (!empty($proyecto['imagen'])) {
                $proyecto['imagen_url'] = 'uploads/' . $proyecto['imagen'];
            } else {
                $proyecto['imagen_url'] = null;
            }
            
            // Eliminar el campo imagen original para no duplicar información
            unset($proyecto['imagen']);
        }
        
        // Enviar respuesta exitosa
        sendSuccessResponse($proyectos, 'Proyectos obtenidos correctamente');
        
    } catch (Exception $e) {
        // Error en la base de datos
        sendErrorResponse('Error interno del servidor', 500);
    }
}

// ========================================
// FUNCIÓN PARA CREAR PROYECTO (PROTEGIDO)
// ========================================
/**
 * MANEJA LA CREACIÓN DE NUEVOS PROYECTOS
 * ======================================
 * Requiere autenticación y valida todos los campos
 */
function handleCreateProject() {
    // Verificar que el método sea POST
    validateHTTPMethod('POST');
    
    // Verificar autenticación del usuario
    $user = getCurrentUserFromToken();
    
    // Obtener datos del body de la petición
    $data = getRequestBody();
    
    // ========================================
    // VALIDACIÓN DE DATOS REQUERIDOS
    // ========================================
    // Verificar campos obligatorios
    if (!isset($data['titulo']) || !isset($data['descripcion'])) {
        sendErrorResponse('Título y descripción son requeridos', 400);
    }
    
    // Sanitizar y validar datos
    $titulo = trim($data['titulo']);
    $descripcion = trim($data['descripcion']);
    $tecnologias = trim($data['tecnologias'] ?? '');
    $url_github = trim($data['url_github'] ?? '');
    $url_produccion = trim($data['url_produccion'] ?? '');
    $estado = $data['estado'] ?? 'activo';
    $orden_mostrar = intval($data['orden_mostrar'] ?? 0);
    
    // Validar que los campos obligatorios no estén vacíos
    if (empty($titulo) || empty($descripcion)) {
        sendErrorResponse('Título y descripción no pueden estar vacíos', 400);
    }
    
    // Validar longitud de campos
    if (strlen($titulo) > 200) {
        sendErrorResponse('El título no puede exceder 200 caracteres', 400);
    }
    
    if (strlen($descripcion) > 1000) {
        sendErrorResponse('La descripción no puede exceder 1000 caracteres', 400);
    }
    
    // ========================================
    // MANEJO DE IMAGEN (SI SE PROPORCIONA)
    // ========================================
    $imagen = '';
    
    // Verificar si se proporcionó una imagen en base64
    if (!empty($data['imagen'])) {
        // Decodificar imagen base64
        $imageData = base64_decode($data['imagen']);
        
        if ($imageData === false) {
            sendErrorResponse('Formato de imagen inválido', 400);
        }
        
        // Verificar tamaño de la imagen (máximo 5MB)
        if (strlen($imageData) > 5 * 1024 * 1024) {
            sendErrorResponse('La imagen es demasiado grande (máx 5MB)', 400);
        }
        
        // Generar nombre único para la imagen
        $imgName = uniqid('img_') . '.jpg';
        $imagePath = __DIR__ . '/../uploads/' . $imgName;
        
        // Guardar imagen en el servidor
        if (file_put_contents($imagePath, $imageData) === false) {
            sendErrorResponse('Error al guardar la imagen', 500);
        }
        
        $imagen = $imgName;
    }
    
    // ========================================
    // GUARDADO EN BASE DE DATOS
    // ========================================
    try {
        $db = getDB(); // Obtener conexión a la base de datos
        
        // Preparar consulta SQL para insertar el proyecto
        $stmt = $db->prepare('
            INSERT INTO proyectos (titulo, descripcion, tecnologias, url_github, 
                                  url_produccion, imagen, estado, orden_mostrar) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        // Ejecutar la consulta con todos los datos
        $stmt->execute([$titulo, $descripcion, $tecnologias, $url_github, 
                       $url_produccion, $imagen, $estado, $orden_mostrar]);
        
        // Obtener el ID del proyecto recién creado
        $projectId = $db->lastInsertId();
        
        // ========================================
        // RESPUESTA DE ÉXITO
        // ========================================
        $responseData = [
            'id' => $projectId,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'tecnologias' => $tecnologias,
            'url_github' => $url_github,
            'url_produccion' => $url_produccion,
            'imagen_url' => $imagen ? 'uploads/' . $imagen : null,
            'estado' => $estado,
            'orden_mostrar' => $orden_mostrar,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Enviar respuesta exitosa
        sendSuccessResponse($responseData, 'Proyecto creado correctamente', 201);
        
    } catch (Exception $e) {
        // Error en la base de datos
        sendErrorResponse('Error interno del servidor', 500);
    }
}

// ========================================
// FUNCIÓN PARA ACTUALIZAR PROYECTO (PROTEGIDO)
// ========================================
/**
 * MANEJA LA ACTUALIZACIÓN DE PROYECTOS EXISTENTES
 * ===============================================
 * Requiere autenticación y valida todos los campos
 */
function handleUpdateProject() {
    // Verificar que el método sea PUT
    validateHTTPMethod('PUT');
    
    // Verificar autenticación del usuario
    $user = getCurrentUserFromToken();
    
    // Obtener datos del body de la petición
    $data = getRequestBody();
    
    // ========================================
    // VALIDACIÓN DE ID DEL PROYECTO
    // ========================================
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        sendErrorResponse('ID de proyecto válido es requerido', 400);
    }
    
    $projectId = intval($data['id']);
    
    // ========================================
    // VERIFICACIÓN DE EXISTENCIA DEL PROYECTO
    // ========================================
    try {
        $db = getDB(); // Obtener conexión a la base de datos
        
        // Verificar que el proyecto existe
        $stmt = $db->prepare('SELECT * FROM proyectos WHERE id = ?');
        $stmt->execute([$projectId]);
        $existingProject = $stmt->fetch();
        
        if (!$existingProject) {
            sendErrorResponse('Proyecto no encontrado', 404);
        }
        
    } catch (Exception $e) {
        sendErrorResponse('Error interno del servidor', 500);
    }
    
    // ========================================
    // VALIDACIÓN Y SANITIZACIÓN DE DATOS
    // ========================================
    $titulo = trim($data['titulo'] ?? $existingProject['titulo']);
    $descripcion = trim($data['descripcion'] ?? $existingProject['descripcion']);
    $tecnologias = trim($data['tecnologias'] ?? $existingProject['tecnologias']);
    $url_github = trim($data['url_github'] ?? $existingProject['url_github']);
    $url_produccion = trim($data['url_produccion'] ?? $existingProject['url_produccion']);
    $estado = $data['estado'] ?? $existingProject['estado'];
    $orden_mostrar = intval($data['orden_mostrar'] ?? $existingProject['orden_mostrar']);
    $imagen = $existingProject['imagen']; // Mantener imagen actual por defecto
    
    // Validar campos obligatorios
    if (empty($titulo) || empty($descripcion)) {
        sendErrorResponse('Título y descripción no pueden estar vacíos', 400);
    }
    
    // ========================================
    // MANEJO DE NUEVA IMAGEN (SI SE PROPORCIONA)
    // ========================================
    if (!empty($data['imagen'])) {
        // Decodificar imagen base64
        $imageData = base64_decode($data['imagen']);
        
        if ($imageData === false) {
            sendErrorResponse('Formato de imagen inválido', 400);
        }
        
        // Verificar tamaño de la imagen
        if (strlen($imageData) > 5 * 1024 * 1024) {
            sendErrorResponse('La imagen es demasiado grande (máx 5MB)', 400);
        }
        
        // Eliminar imagen anterior si existe
        if (!empty($existingProject['imagen'])) {
            $oldImagePath = __DIR__ . '/../uploads/' . $existingProject['imagen'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        
        // Generar nombre único para la nueva imagen
        $imgName = uniqid('img_') . '.jpg';
        $imagePath = __DIR__ . '/../uploads/' . $imgName;
        
        // Guardar nueva imagen
        if (file_put_contents($imagePath, $imageData) === false) {
            sendErrorResponse('Error al guardar la imagen', 500);
        }
        
        $imagen = $imgName;
    }
    
    // ========================================
    // ACTUALIZACIÓN EN BASE DE DATOS
    // ========================================
    try {
        // Preparar consulta SQL para actualizar el proyecto
        $stmt = $db->prepare('
            UPDATE proyectos 
            SET titulo = ?, descripcion = ?, tecnologias = ?, url_github = ?, 
                url_produccion = ?, imagen = ?, estado = ?, orden_mostrar = ?, 
                updated_at = NOW() 
            WHERE id = ?
        ');
        
        // Ejecutar la consulta
        $stmt->execute([$titulo, $descripcion, $tecnologias, $url_github, 
                       $url_produccion, $imagen, $estado, $orden_mostrar, $projectId]);
        
        // ========================================
        // RESPUESTA DE ÉXITO
        // ========================================
        $responseData = [
            'id' => $projectId,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'tecnologias' => $tecnologias,
            'url_github' => $url_github,
            'url_produccion' => $url_produccion,
            'imagen_url' => $imagen ? 'uploads/' . $imagen : null,
            'estado' => $estado,
            'orden_mostrar' => $orden_mostrar,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Enviar respuesta exitosa
        sendSuccessResponse($responseData, 'Proyecto actualizado correctamente');
        
    } catch (Exception $e) {
        // Error en la base de datos
        sendErrorResponse('Error interno del servidor', 500);
    }
}

// ========================================
// FUNCIÓN PARA ELIMINAR PROYECTO (PROTEGIDO)
// ========================================
/**
 * MANEJA LA ELIMINACIÓN DE PROYECTOS
 * ==================================
 * Requiere autenticación y elimina imagen asociada
 */
function handleDeleteProject() {
    // Verificar que el método sea DELETE
    validateHTTPMethod('DELETE');
    
    // Verificar autenticación del usuario
    $user = getCurrentUserFromToken();
    
    // Obtener ID del proyecto desde la URL
    $projectId = intval($_GET['id'] ?? 0);
    
    if (!$projectId) {
        sendErrorResponse('ID de proyecto válido es requerido', 400);
    }
    
    try {
        $db = getDB(); // Obtener conexión a la base de datos
        
        // ========================================
        // OBTENCIÓN DE DATOS DEL PROYECTO
        // ========================================
        // Obtener información del proyecto antes de eliminarlo
        $stmt = $db->prepare('SELECT imagen FROM proyectos WHERE id = ?');
        $stmt->execute([$projectId]);
        $project = $stmt->fetch();
        
        if (!$project) {
            sendErrorResponse('Proyecto no encontrado', 404);
        }
        
        // ========================================
        // ELIMINACIÓN DE IMAGEN DEL SERVIDOR
        // ========================================
        if (!empty($project['imagen'])) {
            $imagePath = __DIR__ . '/../uploads/' . $project['imagen'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // ========================================
        // ELIMINACIÓN DE LA BASE DE DATOS
        // ========================================
        $stmt = $db->prepare('DELETE FROM proyectos WHERE id = ?');
        $stmt->execute([$projectId]);
        
        if ($stmt->rowCount() > 0) {
            // Enviar respuesta exitosa
            sendSuccessResponse(null, 'Proyecto eliminado correctamente');
        } else {
            sendErrorResponse('Error al eliminar el proyecto', 500);
        }
        
    } catch (Exception $e) {
        // Error en la base de datos
        sendErrorResponse('Error interno del servidor', 500);
    }
}

// ========================================
// FUNCIÓN PARA OBTENER PROYECTO ESPECÍFICO (PÚBLICO)
// ========================================
/**
 * MANEJA LA OBTENCIÓN DE UN PROYECTO ESPECÍFICO
 * =============================================
 * Retorna los datos de un proyecto por ID
 */
function handleGetProject() {
    // Verificar que el método sea GET
    validateHTTPMethod('GET');
    
    // Obtener ID del proyecto desde la URL
    $projectId = intval($_GET['id'] ?? 0);
    
    if (!$projectId) {
        sendErrorResponse('ID de proyecto válido es requerido', 400);
    }
    
    try {
        $db = getDB(); // Obtener conexión a la base de datos
        
        // Consulta SQL: obtener proyecto específico
        $stmt = $db->prepare('
            SELECT id, titulo, descripcion, tecnologias, url_github, url_produccion, 
                   imagen, estado, orden_mostrar, created_at, updated_at 
            FROM proyectos 
            WHERE id = ? AND estado = "activo"
        ');
        $stmt->execute([$projectId]);
        
        $proyecto = $stmt->fetch();
        
        if (!$proyecto) {
            sendErrorResponse('Proyecto no encontrado', 404);
        }
        
        // ========================================
        // PROCESAMIENTO DE DATOS PARA RESPUESTA
        // ========================================
        // Construir URL completa de la imagen
        if (!empty($proyecto['imagen'])) {
            $proyecto['imagen_url'] = 'uploads/' . $proyecto['imagen'];
        } else {
            $proyecto['imagen_url'] = null;
        }
        
        // Eliminar campo imagen original
        unset($proyecto['imagen']);
        
        // Enviar respuesta exitosa
        sendSuccessResponse($proyecto, 'Proyecto obtenido correctamente');
        
    } catch (Exception $e) {
        // Error en la base de datos
        sendErrorResponse('Error interno del servidor', 500);
    }
} 