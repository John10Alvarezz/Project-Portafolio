<?php
// ========================================
// INDEX.PHP - SITIO PÚBLICO DEL PORTAFOLIO
// ========================================
// Este archivo es la página principal que ven todos los visitantes
// Muestra el portafolio personal con proyectos, información personal y contacto

// Iniciar sesión PHP para manejar variables de sesión
session_start();

// Incluir archivos necesarios para la funcionalidad
require_once 'config/database.php';  // Conexión a la base de datos
require_once 'includes/functions.php'; // Funciones auxiliares (sanitización, redirección, etc.)

// ========================================
// DETECCIÓN DEL ESTADO DE SESIÓN DEL ADMIN
// ========================================
// Determina si el usuario está logueado como administrador
// para mostrar el enlace correcto en la navegación

$panelUrl = 'admin/login.php';  // URL por defecto (login)
$panelText = '<i class="bi bi-gear"></i> Admin';  // Texto por defecto

// Si hay una sesión activa de administrador
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $panelUrl = 'admin/index.php';  // Cambiar a panel de administración
    $panelText = '<i class="bi bi-speedometer2"></i> Panel';  // Cambiar texto
}

// ========================================
// OBTENCIÓN DE PROYECTOS DESDE LA BASE DE DATOS
// ========================================
// Consulta solo los proyectos que están marcados como 'activo'
// y los ordena por orden de mostrar y fecha de creación

try {
    $db = getDB();  // Obtener conexión a la base de datos
    // Consulta SQL: obtener proyectos activos ordenados
    $proyectos = $db->query("SELECT * FROM proyectos WHERE estado = 'activo' ORDER BY orden_mostrar ASC, created_at DESC")->fetchAll();
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
    <title>John Álvarez - Portafolio Personal</title>

    <!-- ========================================
         ENLACES A RECURSOS EXTERNOS
         ======================================== -->
    <!-- Bootstrap 5 - Framework CSS para diseño responsive -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons - Iconos vectoriales -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS personalizado del sitio -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- ========================================
         NAVEGACIÓN PRINCIPAL
         ======================================== -->
    <!-- Barra de navegación fija en la parte superior -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <!-- Logo/Nombre del sitio -->
            <a class="navbar-brand" href="#home">Portafolio de John Álvarez</a>

            <!-- Botón hamburguesa para móviles -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menú de navegación -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Enlaces principales -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Sobre Mí</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#projects">Proyectos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contacto</a>
                    </li>
                </ul>

                <!-- Enlace al panel de administración -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $panelUrl ?>">
                            <?= $panelText ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ========================================
         SECCIÓN HERO (BANNER PRINCIPAL)
         ======================================== -->
    <!-- Sección de bienvenida con gradiente de fondo -->
    <section id="home" class="hero-section bg-primary text-white d-flex align-items-center min-vh-100">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Título principal -->
                    <h1 class="display-4 fw-bold mb-4">👋 ¡Hola! Soy John Álvarez</h1>
                    <!-- Descripción breve -->
                    <p class="lead mb-4">Estudiante de Técnico en Informática apasionado por el desarrollo de software,
                        la gestión de bases de datos y la tecnología en general.</p>

                    <!-- Botones de acción -->
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="#projects" class="btn btn-light btn-lg">
                            <i class="bi bi-collection"></i> Ver Proyectos
                        </a>
                        <a href="#contact" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-envelope"></i> Contactar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================
         SECCIÓN "SOBRE MÍ"
         ======================================== -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <!-- Columna de texto -->
                <div class="col-lg-6">
                    <h2 class="h1 mb-4">Sobre Mí</h2>
                    <p class="lead mb-4">Me gusta aprender constantemente y compartir mis conocimientos a través de
                        proyectos y colaboraciones.</p>
                    <p class="mb-4">Soy estudiante de Técnico en Informática con pasión por el desarrollo de software y
                        la gestión de bases de datos. Me especializo en crear soluciones web funcionales y atractivas.
                    </p>

                    <!-- Lista de tecnologías -->
                    <div class="mb-4">
                        <h5>🛠️ Tecnologías y herramientas</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-primary">HTML5</span>
                            <span class="badge bg-primary">CSS3</span>
                            <span class="badge bg-primary">JavaScript</span>
                            <span class="badge bg-primary">PHP</span>
                            <span class="badge bg-primary">Python</span>
                            <span class="badge bg-primary">C#</span>
                            <span class="badge bg-primary">MySQL</span>
                            <span class="badge bg-primary">ObjectDB</span>
                        </div>
                    </div>
                </div>

                <!-- Columna de imagen -->
                <div class="col-lg-6 text-center">
                    <div class="about-image">
                        <!-- Imagen mia -->
                        <img src="assets/img/imgPerfil.jpg" alt=" John Álvarez - Desarrollador"
                            class="img-fluid rounded-circle shadow" style="max-width: 300px; height: auto;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================
         SECCIÓN DE PROYECTOS
         ======================================== -->
    <section id="projects" class="py-5 bg-light">
        <div class="container">
            <!-- Título de la sección -->
            <div class="text-center mb-5">
                <h2 class="h1">Mis Proyectos</h2>
                <p class="lead">Una muestra de mi trabajo y experiencia en desarrollo</p>
            </div>

            <!-- Contenedor de proyectos -->
            <div class="row" id="projects-container">
                <?php if (empty($proyectos)): ?>
                <!-- Mensaje cuando no hay proyectos -->
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        No hay proyectos disponibles en este momento.
                    </div>
                </div>
                <?php else: ?>
                <!-- Bucle para mostrar cada proyecto -->
                <?php foreach ($proyectos as $proyecto): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <!-- Tarjeta de proyecto -->
                    <div class="card h-100 shadow-sm project-card">
                        <?php if (!empty($proyecto['imagen'])): ?>
                        <!-- Imagen del proyecto si existe -->
                        <img src="uploads/<?= htmlspecialchars($proyecto['imagen']) ?>"
                            class="card-img-top project-image" alt="<?= htmlspecialchars($proyecto['titulo']) ?>"
                            style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                        <!-- Placeholder si no hay imagen -->
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light"
                            style="height: 200px;">
                            <i class="bi bi-image display-4 text-muted"></i>
                        </div>
                        <?php endif; ?>

                        <!-- Contenido de la tarjeta -->
                        <div class="card-body d-flex flex-column">
                            <!-- Título del proyecto -->
                            <h5 class="card-title"><?= htmlspecialchars($proyecto['titulo']) ?></h5>

                            <!-- Descripción del proyecto (truncada a 120 caracteres) -->
                            <p class="card-text flex-grow-1">
                                <?= htmlspecialchars(truncateText($proyecto['descripcion'], 120)) ?>
                            </p>

                            <!-- Tecnologías utilizadas -->
                            <?php if (!empty($proyecto['tecnologias'])): ?>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-tools"></i> <?= htmlspecialchars($proyecto['tecnologias']) ?>
                                </small>
                            </div>
                            <?php endif; ?>

                            <!-- Botones de acción -->
                            <div class="d-flex gap-2 mt-auto">
                                <?php if (!empty($proyecto['url_github'])): ?>
                                <!-- Enlace a GitHub -->
                                <a href="<?= htmlspecialchars($proyecto['url_github']) ?>"
                                    class="btn btn-outline-dark btn-sm" target="_blank">
                                    <i class="bi bi-github"></i> GitHub
                                </a>
                                <?php endif; ?>

                                <?php if (!empty($proyecto['url_produccion'])): ?>
                                <!-- Enlace al demo en vivo -->
                                <a href="<?= htmlspecialchars($proyecto['url_produccion']) ?>"
                                    class="btn btn-primary btn-sm" target="_blank">
                                    <i class="bi bi-eye"></i> Ver Demo
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ========================================
         SECCIÓN DE CONTACTO
         ======================================== -->
    <section id="contact" class="py-5">
        <div class="container">
            <!-- Título de la sección -->
            <div class="text-center mb-5">
                <h2 class="h1">Contacto</h2>
                <p class="lead">¿Te gustaría colaborar o tienes alguna pregunta? ¡No dudes en contactarme!</p>
            </div>

            <!-- Tarjetas de contacto -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="row g-4">
                        <!-- Tarjeta de Email -->
                        <div class="col-md-4 text-center">
                            <div class="contact-card p-4 h-100">
                                <i class="bi bi-envelope display-4 text-primary mb-3"></i>
                                <h5>Email</h5>
                                <p class="mb-3">Envíame un correo</p>
                                <a href="mailto:jalvarez2023@alu.uct.cl" class="btn btn-primary">
                                    <i class="bi bi-envelope"></i> jalvarez2023@alu.uct.cl
                                </a>
                            </div>
                        </div>

                        <!-- Tarjeta de GitHub -->
                        <div class="col-md-4 text-center">
                            <div class="contact-card p-4 h-100">
                                <i class="bi bi-github display-4 text-dark mb-3"></i>
                                <h5>GitHub</h5>
                                <p class="mb-3">Revisa mis proyectos</p>
                                <a href="https://github.com/John10Alvarezz" class="btn btn-dark" target="_blank">
                                    <i class="bi bi-github"></i> @John10Alvarezz
                                </a>
                            </div>
                        </div>

                        <!-- Tarjeta de Instagram -->
                        <div class="col-md-4 text-center">
                            <div class="contact-card p-4 h-100">
                                <i class="bi bi-instagram display-4 text-danger mb-3"></i>
                                <h5>Instagram</h5>
                                <p class="mb-3">Sígueme en Instagram</p>
                                <a href="https://instagram.com/john._.alvarez_" class="btn btn-danger" target="_blank">
                                    <i class="bi bi-instagram"></i> @ john._.alvarez_
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================
         FOOTER
         ======================================== -->
    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">&copy; 2024 John Álvarez. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- ========================================
         SCRIPTS JAVASCRIPT
         ======================================== -->
    <!-- Bootstrap JS - Para funcionalidades como el menú hamburguesa -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JavaScript personalizado -->
    <script src="assets/js/main.js"></script>
</body>

</html>