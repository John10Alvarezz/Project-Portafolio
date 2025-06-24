<?php
// ========================================
// ADMIN/ELIMINAR-PROYECTO.PHP - ELIMINAR PROYECTO
// ========================================
// Este archivo maneja la eliminación segura de proyectos
// Incluye eliminación de la imagen asociada y confirmación

// Iniciar sesión PHP para manejar variables de sesión
session_start();

// Incluir archivos necesarios para la funcionalidad
require_once '../config/database.php';  // Conexión a la base de datos
require_once '../includes/functions.php'; // Funciones auxiliares

// ========================================
// VERIFICACIÓN DE AUTENTICACIÓN
// ========================================
// Verificar que el usuario esté logueado, si no, redirigir al login
if (!isLoggedIn()) {
    redirect('login.php');
}

// ========================================
// OBTENCIÓN DEL ID DEL PROYECTO
// ========================================
// Obtener el ID del proyecto desde la URL y convertirlo a entero
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verificar que se proporcionó un ID válido
if (!$id) {
    redirect('proyectos.php', 'ID de proyecto no válido', 'error');
}

// ========================================
// PROCESO DE ELIMINACIÓN
// ========================================
try {
    $db = getDB(); // Obtener conexión a la base de datos
    
    // ========================================
    // OBTENCIÓN DE DATOS DEL PROYECTO
    // ========================================
    // Primero obtener los datos del proyecto para eliminar la imagen
    $stmt = $db->prepare('SELECT imagen FROM proyectos WHERE id = ?');
    $stmt->execute([$id]); // Ejecutar con el ID del proyecto
    
    $proyecto = $stmt->fetch(); // Obtener resultado
    
    // Verificar si el proyecto existe
    if (!$proyecto) {
        redirect('proyectos.php', 'Proyecto no encontrado', 'error');
    }
    
    // ========================================
    // ELIMINACIÓN DE LA IMAGEN DEL SERVIDOR
    // ========================================
    // Si el proyecto tiene una imagen asociada, eliminarla del servidor
    if (!empty($proyecto['imagen'])) {
        $imagePath = __DIR__ . '/../uploads/' . $proyecto['imagen'];
        
        // Verificar si el archivo existe antes de intentar eliminarlo
        if (file_exists($imagePath)) {
            unlink($imagePath); // Eliminar archivo de imagen
        }
    }
    
    // ========================================
    // ELIMINACIÓN DE LA BASE DE DATOS
    // ========================================
    // Preparar consulta SQL para eliminar el proyecto
    $stmt = $db->prepare('DELETE FROM proyectos WHERE id = ?');
    $stmt->execute([$id]); // Ejecutar con el ID del proyecto
    
    // Verificar si se eliminó correctamente
    if ($stmt->rowCount() > 0) {
        // Proyecto eliminado exitosamente
        redirect('proyectos.php', 'Proyecto eliminado correctamente', 'success');
    } else {
        // No se pudo eliminar el proyecto
        redirect('proyectos.php', 'Error al eliminar el proyecto', 'error');
    }
    
} catch (Exception $e) {
    // Si hay error en la base de datos
    redirect('proyectos.php', 'Error interno del servidor', 'error');
}
?> 