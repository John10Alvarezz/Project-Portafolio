<?php
// ========================================
// ADMIN/EDITAR-PROYECTO.PHP - EDITAR PROYECTO EXISTENTE
// ========================================
// Este archivo maneja la edición de proyectos existentes
// Permite modificar todos los campos y actualizar la imagen

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
    redirect('proyectos.php', 'Proyecto no especificado', 'error');
}

// ========================================
// VARIABLES PARA MANEJAR ERRORES Y DATOS
// ========================================
$error = '';      // Variable para almacenar mensajes de error
$proyecto = null; // Variable para almacenar los datos del proyecto

// ========================================
// OBTENCIÓN DE DATOS DEL PROYECTO
// ========================================
// Consultar la base de datos para obtener los datos del proyecto
try {
    $db = getDB(); // Obtener conexión a la base de datos
    
    // Preparar consulta SQL para obtener el proyecto por ID
    $stmt = $db->prepare('SELECT * FROM proyectos WHERE id = ?');
    $stmt->execute([$id]); // Ejecutar con el ID del proyecto
    
    $proyecto = $stmt->fetch(); // Obtener resultado
    
    // Verificar si el proyecto existe
    if (!$proyecto) {
        redirect('proyectos.php', 'Proyecto no encontrado', 'error');
    }
    
} catch (Exception $e) {
    // Si hay error en la base de datos
    redirect('proyectos.php', 'Error al cargar el proyecto', 'error');
}

// ========================================
// PROCESAMIENTO DEL FORMULARIO DE EDICIÓN
// ========================================
// Verificar si se envió el formulario (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ========================================
    // OBTENCIÓN Y SANITIZACIÓN DE DATOS
    // ========================================
    // Obtener todos los campos del formulario y sanitizarlos
    $titulo = sanitizeInput($_POST['titulo'] ?? '');           // Título del proyecto
    $descripcion = sanitizeInput($_POST['descripcion'] ?? ''); // Descripción del proyecto
    $tecnologias = sanitizeInput($_POST['tecnologias'] ?? ''); // Tecnologías utilizadas
    $url_github = sanitizeInput($_POST['url_github'] ?? '');   // URL del repositorio GitHub
    $url_produccion = sanitizeInput($_POST['url_produccion'] ?? ''); // URL del demo en vivo
    $estado = $_POST['estado'] ?? 'activo';                    // Estado del proyecto
    $orden_mostrar = intval($_POST['orden_mostrar'] ?? 0);     // Orden de visualización
    $imagen = $proyecto['imagen']; // Mantener la imagen actual por defecto
    
    // ========================================
    // VALIDACIÓN DE DATOS OBLIGATORIOS
    // ========================================
    // Verificar que los campos requeridos no estén vacíos
    if (empty($titulo) || empty($descripcion)) {
        $error = 'Título y descripción son obligatorios.';
    } else {
        // ========================================
        // MANEJO DE SUBIDA DE NUEVA IMAGEN
        // ========================================
        // Verificar si se subió una nueva imagen
        if (!empty($_FILES['imagen']['name'])) {
            $file = $_FILES['imagen']; // Obtener información del archivo
            
            // ========================================
            // VALIDACIÓN DE TIPO DE ARCHIVO
            // ========================================
            // Lista de tipos MIME permitidos
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            // Verificar si el tipo de archivo está permitido
            if (!in_array($file['type'], $allowed)) {
                $error = 'Tipo de imagen no permitido.';
            }
            // ========================================
            // VALIDACIÓN DE TAMAÑO DE ARCHIVO
            // ========================================
            // Verificar que el archivo no exceda 5MB
            elseif ($file['size'] > 5 * 1024 * 1024) {
                $error = 'La imagen es demasiado grande (máx 5MB).';
            } else {
                // ========================================
                // PROCESAMIENTO DE LA NUEVA IMAGEN
                // ========================================
                // Obtener la extensión del archivo original
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                
                // Generar nombre único para la nueva imagen
                $imgName = uniqid('img_') . '.' . $ext;
                
                // Definir la ruta de destino en la carpeta uploads
                $dest = __DIR__ . '/../uploads/' . $imgName;
                
                // Intentar mover el archivo subido a la carpeta de destino
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    // ========================================
                    // ELIMINACIÓN DE LA IMAGEN ANTERIOR
                    // ========================================
                    // Si había una imagen anterior, eliminarla del servidor
                    if (!empty($proyecto['imagen']) && file_exists(__DIR__ . '/../uploads/' . $proyecto['imagen'])) {
                        unlink(__DIR__ . '/../uploads/' . $proyecto['imagen']);
                    }
                    
                    $imagen = $imgName; // Actualizar con la nueva imagen
                } else {
                    $error = 'Error al subir la imagen.';
                }
            }
        }
    }
    
    // ========================================
    // ACTUALIZACIÓN EN BASE DE DATOS
    // ========================================
    // Si no hay errores, proceder a actualizar en la base de datos
    if (!$error) {
        try {
            // Preparar consulta SQL para actualizar el proyecto
            $stmt = $db->prepare('UPDATE proyectos SET titulo=?, descripcion=?, tecnologias=?, url_github=?, url_produccion=?, imagen=?, estado=?, orden_mostrar=? WHERE id=?');
            
            // Ejecutar la consulta con todos los datos actualizados
            $stmt->execute([$titulo, $descripcion, $tecnologias, $url_github, $url_produccion, $imagen, $estado, $orden_mostrar, $id]);
            
            // Redirigir a la lista de proyectos con mensaje de éxito
            redirect('proyectos.php', 'Proyecto actualizado correctamente', 'success');
            
        } catch (Exception $e) {
            // Si hay error en la base de datos
            $error = 'Error al actualizar el proyecto.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proyecto - Admin</title>
    
    <!-- ========================================
         ENLACES A RECURSOS EXTERNOS
         ======================================== -->
    <!-- Bootstrap 5 - Framework CSS para diseño responsive -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS personalizado del panel de administración -->
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <!-- ========================================
         NAVEGACIÓN DEL PANEL ADMIN
         ======================================== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <!-- Logo/Nombre del panel -->
            <a class="navbar-brand" href="index.php">Admin Portafolio</a>
            
            <!-- Enlaces de navegación -->
            <div class="navbar-nav ms-auto">
                <!-- Enlace para volver al sitio público -->
                <a class="nav-link text-white" href="../index.php" target="_blank">Volver al sitio público</a>
                
                <!-- Enlace para cerrar sesión -->
                <a class="nav-link text-white" href="logout.php">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <!-- ========================================
         CONTENIDO PRINCIPAL
         ======================================== -->
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- ========================================
                     TARJETA DEL FORMULARIO
                     ======================================== -->
                <div class="card">
                    <!-- Encabezado de la tarjeta -->
                    <div class="card-header">Editar Proyecto</div>
                    
                    <div class="card-body">
                        <!-- ========================================
                             MENSAJES DE ERROR
                             ======================================== -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- ========================================
                             FORMULARIO DE EDICIÓN
                             ======================================== -->
                        <form method="POST" enctype="multipart/form-data">
                            <!-- Campo: Título del proyecto -->
                            <div class="mb-3">
                                <label class="form-label">Título *</label>
                                <input type="text" 
                                       name="titulo" 
                                       class="form-control" 
                                       required 
                                       maxlength="200" 
                                       value="<?= htmlspecialchars($proyecto['titulo']) ?>">
                            </div>
                            
                            <!-- Campo: Descripción del proyecto -->
                            <div class="mb-3">
                                <label class="form-label">Descripción *</label>
                                <textarea name="descripcion" 
                                          class="form-control" 
                                          rows="4" 
                                          required 
                                          maxlength="1000"><?= htmlspecialchars($proyecto['descripcion']) ?></textarea>
                            </div>
                            
                            <!-- Campo: Tecnologías utilizadas -->
                            <div class="mb-3">
                                <label class="form-label">Tecnologías</label>
                                <input type="text" 
                                       name="tecnologias" 
                                       class="form-control" 
                                       maxlength="300" 
                                       value="<?= htmlspecialchars($proyecto['tecnologias']) ?>">
                            </div>
                            
                            <!-- Campo: URL del repositorio GitHub -->
                            <div class="mb-3">
                                <label class="form-label">URL GitHub</label>
                                <input type="url" 
                                       name="url_github" 
                                       class="form-control" 
                                       maxlength="300" 
                                       value="<?= htmlspecialchars($proyecto['url_github']) ?>">
                            </div>
                            
                            <!-- Campo: URL del demo en vivo -->
                            <div class="mb-3">
                                <label class="form-label">URL Producción</label>
                                <input type="url" 
                                       name="url_produccion" 
                                       class="form-control" 
                                       maxlength="300" 
                                       value="<?= htmlspecialchars($proyecto['url_produccion']) ?>">
                            </div>
                            
                            <!-- Campo: Imagen actual del proyecto -->
                            <div class="mb-3">
                                <label class="form-label">Imagen actual</label><br>
                                <?php if (!empty($proyecto['imagen'])): ?>
                                    <!-- Mostrar imagen actual si existe -->
                                    <img src="../uploads/<?= htmlspecialchars($proyecto['imagen']) ?>" 
                                         alt="Imagen actual" 
                                         style="max-width:150px;max-height:100px;">
                                <?php else: ?>
                                    <!-- Mensaje si no hay imagen -->
                                    <span class="text-muted">No hay imagen</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Campo: Nueva imagen del proyecto -->
                            <div class="mb-3">
                                <label class="form-label">Nueva Imagen (opcional)</label>
                                <input type="file" 
                                       name="imagen" 
                                       class="form-control">
                            </div>
                            
                            <!-- Campo: Estado del proyecto -->
                            <div class="mb-3">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="activo" <?= $proyecto['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                                    <option value="inactivo" <?= $proyecto['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                            
                            <!-- Campo: Orden de visualización -->
                            <div class="mb-3">
                                <label class="form-label">Orden de Mostrar</label>
                                <input type="number" 
                                       name="orden_mostrar" 
                                       class="form-control" 
                                       value="<?= $proyecto['orden_mostrar'] ?>" 
                                       min="0">
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="d-grid">
                                <!-- Botón para guardar los cambios -->
                                <button type="submit" class="btn btn-success">Actualizar Proyecto</button>
                                
                                <!-- Enlace para volver a la lista -->
                                <a href="proyectos.php" class="btn btn-link mt-2">Volver al listado</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 