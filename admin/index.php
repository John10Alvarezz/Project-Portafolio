<?php
// ========================================
// ADMIN/INDEX.PHP - DASHBOARD DE ADMINISTRACIÓN
// ========================================
// Este archivo es el panel principal de administración
// Muestra estadísticas, resumen de proyectos y acceso rápido a funciones

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
// OBTENCIÓN DE ESTADÍSTICAS
// ========================================
// Consultar la base de datos para obtener estadísticas del portafolio

try {
    $db = getDB(); // Obtener conexión a la base de datos
    
    // Contar total de proyectos
    $totalProyectos = $db->query('SELECT COUNT(*) FROM proyectos')->fetchColumn();
    
    // Contar proyectos activos (visibles en el sitio público)
    $activos = $db->query("SELECT COUNT(*) FROM proyectos WHERE estado = 'activo'")->fetchColumn();
    
    // Contar proyectos inactivos (ocultos del sitio público)
    $inactivos = $db->query("SELECT COUNT(*) FROM proyectos WHERE estado = 'inactivo'")->fetchColumn();
    
    // Obtener los 5 proyectos más recientes para mostrar en el dashboard
    $ultimos = $db->query("SELECT titulo, created_at FROM proyectos ORDER BY created_at DESC LIMIT 5")->fetchAll();
    
} catch (Exception $e) {
    // Si hay error en la base de datos, usar valores por defecto
    $totalProyectos = $activos = $inactivos = 0;
    $ultimos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    
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
            <a class="navbar-brand" href="#">Admin Portafolio</a>
            
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
         LAYOUT PRINCIPAL DEL DASHBOARD
         ======================================== -->
    <div class="container-fluid">
        <div class="row">
            <!-- ========================================
                 BARRA LATERAL (SIDEBAR)
                 ======================================== -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar py-4">
                <ul class="nav flex-column">
                    <!-- Enlace activo al dashboard -->
                    <li class="nav-item mb-2">
                        <a class="nav-link active" href="index.php">Dashboard</a>
                    </li>
                    
                    <!-- Enlace para gestionar proyectos -->
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="proyectos.php">Gestionar Proyectos</a>
                    </li>
                    
                    <!-- Enlace para volver al sitio público -->
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="../index.php">Volver al sitio público</a>
                    </li>
                </ul>
            </nav>

            <!-- ========================================
                 CONTENIDO PRINCIPAL
                 ======================================== -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Título principal del dashboard -->
                <h1 class="h2 mb-4">Dashboard</h1>
                
                <!-- Mostrar mensajes flash (éxito, error, etc.) -->
                <?php showFlashMessage(); ?>
                
                <!-- ========================================
                     TARJETAS DE ESTADÍSTICAS
                     ======================================== -->
                <div class="row mb-4">
                    <!-- Tarjeta: Total de proyectos -->
                    <div class="col-md-4">
                        <div class="card text-bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Proyectos</h5>
                                <h2><?= $totalProyectos ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tarjeta: Proyectos activos -->
                    <div class="col-md-4">
                        <div class="card text-bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Activos</h5>
                                <h2><?= $activos ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tarjeta: Proyectos inactivos -->
                    <div class="col-md-4">
                        <div class="card text-bg-warning mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Inactivos</h5>
                                <h2><?= $inactivos ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================
                     SECCIÓN DE ÚLTIMOS PROYECTOS
                     ======================================== -->
                <div class="card mb-4">
                    <div class="card-header">Últimos proyectos</div>
                    <div class="card-body">
                        <?php if (empty($ultimos)): ?>
                            <!-- Mensaje cuando no hay proyectos -->
                            <p class="text-muted">No hay proyectos registrados.</p>
                        <?php else: ?>
                            <!-- Grid de tarjetas de proyectos recientes -->
                            <div class="row g-3">
                                <?php foreach ($ultimos as $p): ?>
                                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                                        <!-- Tarjeta de proyecto individual -->
                                        <div class="card h-100 shadow-sm border-0">
                                            <?php
                                            // ========================================
                                            // OBTENCIÓN DE IMAGEN DEL PROYECTO
                                            // ========================================
                                            // Consultar la base de datos para obtener la imagen
                                            // de este proyecto específico
                                            
                                            $img = ''; // Variable para almacenar la ruta de la imagen
                                            
                                            // Preparar consulta para obtener la imagen
                                            $stmtImg = $db->prepare('SELECT imagen FROM proyectos WHERE titulo = ? LIMIT 1');
                                            $stmtImg->execute([$p['titulo']]); // Ejecutar con el título del proyecto
                                            $imgRow = $stmtImg->fetch(); // Obtener resultado
                                            
                                            // Si hay imagen, construir la ruta completa
                                            if ($imgRow && !empty($imgRow['imagen'])) {
                                                $img = '../uploads/' . htmlspecialchars($imgRow['imagen']);
                                            }
                                            ?>
                                            
                                            <?php if ($img): ?>
                                                <!-- Mostrar imagen del proyecto si existe -->
                                                <img src="<?= $img ?>" class="card-img-top" style="height:90px;object-fit:cover;" alt="Imagen proyecto">
                                            <?php else: ?>
                                                <!-- Placeholder si no hay imagen -->
                                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:90px;">
                                                    <i class="bi bi-image text-muted display-6"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Contenido de la tarjeta -->
                                            <div class="card-body p-2">
                                                <!-- Título del proyecto (truncado si es muy largo) -->
                                                <h6 class="card-title mb-1" style="font-size:1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                    <?= htmlspecialchars($p['titulo']) ?>
                                                </h6>
                                                
                                                <!-- Fecha de creación formateada -->
                                                <small class="text-muted">
                                                    <?= formatDate($p['created_at'], 'd/m/Y') ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html> 