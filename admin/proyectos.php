<?php
// ========================================
// ADMIN/PROYECTOS.PHP - GESTIÓN DE PROYECTOS
// ========================================
// Este archivo maneja la lista y gestión de todos los proyectos
// Permite ver, editar y eliminar proyectos del portafolio

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

// Obtener información del usuario actual para mostrar en la interfaz
$user = getCurrentUser();

// ========================================
// OBTENCIÓN DE PROYECTOS
// ========================================
// Consultar la base de datos para obtener todos los proyectos
// ordenados por fecha de creación (más recientes primero)

try {
    $db = getDB(); // Obtener conexión a la base de datos
    
    // Consulta SQL: obtener todos los proyectos ordenados por fecha
    $proyectos = $db->query('SELECT * FROM proyectos ORDER BY created_at DESC')->fetchAll();
    
} catch (Exception $e) {
    // Si hay error en la base de datos, usar array vacío
    $proyectos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyectos - Admin</title>
    
    <!-- ========================================
         ENLACES A RECURSOS EXTERNOS
         ======================================== -->
    <!-- Bootstrap 5 - Framework CSS para diseño responsive -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS personalizado del panel de administración -->
    <link href="../assets/css/admin.css" rel="stylesheet">
    
    <!-- ========================================
         SCRIPT DE CONFIRMACIÓN DE ELIMINACIÓN
         ======================================== -->
    <script>
    /**
     * FUNCIÓN DE CONFIRMACIÓN DE ELIMINACIÓN
     * ======================================
     * Muestra un diálogo de confirmación antes de eliminar un proyecto
     * 
     * @param {number} id - ID del proyecto a eliminar
     * @param {string} titulo - Título del proyecto para mostrar en la confirmación
     */
    function confirmarEliminar(id, titulo) {
        // Mostrar diálogo de confirmación con el título del proyecto
        if (confirm('¿Seguro que deseas eliminar el proyecto "' + titulo + '"? Esta acción no se puede deshacer.')) {
            // Si el usuario confirma, redirigir a la página de eliminación
            window.location.href = 'eliminar-proyecto.php?id=' + id;
        }
    }
    </script>
</head>
<body>
    <!-- ========================================
         NAVEGACIÓN DEL PANEL ADMIN
         ======================================== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <!-- Logo/Nombre del panel -->
            <a class="navbar-brand" href="index.php">Admin Portafolio</a>
            
            <!-- Enlaces de navegación y usuario -->
            <div class="navbar-nav ms-auto">
                <!-- Enlace para volver al sitio público -->
                <a class="nav-link text-white" href="../index.php">Volver al sitio público</a>
                
                <!-- Mostrar nombre del usuario logueado -->
                <span class="nav-link text-white">👤 <?= htmlspecialchars($user['nombre']) ?></span>
                
                <!-- Enlace para cerrar sesión -->
                <a class="nav-link text-white" href="logout.php">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <!-- ========================================
         LAYOUT PRINCIPAL
         ======================================== -->
    <div class="container-fluid">
        <div class="row">
            <!-- ========================================
                 BARRA LATERAL (SIDEBAR)
                 ======================================== -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar py-4">
                <ul class="nav flex-column">
                    <!-- Enlace al dashboard -->
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    
                    <!-- Enlace activo para gestionar proyectos -->
                    <li class="nav-item mb-2">
                        <a class="nav-link active" href="proyectos.php">Gestionar Proyectos</a>
                    </li>
                </ul>
            </nav>

            <!-- ========================================
                 CONTENIDO PRINCIPAL
                 ======================================== -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- ========================================
                     CABECERA CON TÍTULO Y BOTÓN DE AGREGAR
                     ======================================== -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Proyectos</h1>
                    <!-- Botón para agregar nuevo proyecto -->
                    <a href="agregar-proyecto.php" class="btn btn-primary">Agregar Proyecto</a>
                </div>
                
                <!-- Mostrar mensajes flash (éxito, error, etc.) -->
                <?php showFlashMessage(); ?>
                
                <!-- ========================================
                     TABLA DE PROYECTOS
                     ======================================== -->
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($proyectos)): ?>
                            <!-- Mensaje cuando no hay proyectos -->
                            <p class="text-muted">No hay proyectos registrados.</p>
                        <?php else: ?>
                            <!-- Tabla responsive con todos los proyectos -->
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <!-- ========================================
                                         ENCABEZADOS DE LA TABLA
                                         ======================================== -->
                                    <thead>
                                        <tr>
                                            <th>ID</th>           <!-- Identificador único -->
                                            <th>Título</th>       <!-- Título del proyecto -->
                                            <th>Estado</th>       <!-- Activo/Inactivo -->
                                            <th>Creado</th>       <!-- Fecha de creación -->
                                            <th>Acciones</th>     <!-- Botones de editar/eliminar -->
                                        </tr>
                                    </thead>
                                    
                                    <!-- ========================================
                                         CUERPO DE LA TABLA
                                         ======================================== -->
                                    <tbody>
                                        <?php foreach ($proyectos as $p): ?>
                                            <tr>
                                                <!-- ID del proyecto -->
                                                <td><?= $p['id'] ?></td>
                                                
                                                <!-- Título del proyecto -->
                                                <td><?= htmlspecialchars($p['titulo']) ?></td>
                                                
                                                <!-- Estado del proyecto con badge de color -->
                                                <td>
                                                    <span class="badge <?= $p['estado'] === 'activo' ? 'bg-success' : 'bg-warning' ?>">
                                                        <?= ucfirst($p['estado']) ?>
                                                    </span>
                                                </td>
                                                
                                                <!-- Fecha de creación formateada -->
                                                <td><?= formatDate($p['created_at']) ?></td>
                                                
                                                <!-- Botones de acción -->
                                                <td>
                                                    <!-- Botón de editar -->
                                                    <a href="editar-proyecto.php?id=<?= $p['id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        Editar
                                                    </a>
                                                    
                                                    <!-- Botón de eliminar con confirmación -->
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmarEliminar(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['titulo'])) ?>')">
                                                        Eliminar
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html> 