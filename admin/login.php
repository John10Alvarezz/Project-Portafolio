<?php
// ========================================
// ADMIN/LOGIN.PHP - PÁGINA DE INICIO DE SESIÓN
// ========================================
// Este archivo maneja la autenticación de usuarios para acceder
// al panel de administración del portafolio

// Iniciar sesión PHP para manejar variables de sesión
session_start();

// Incluir archivos necesarios para la funcionalidad
require_once '../config/database.php';  // Conexión a la base de datos
require_once '../includes/functions.php'; // Funciones auxiliares

// ========================================
// VERIFICACIÓN DE SESIÓN ACTIVA
// ========================================
// Si el usuario ya está logueado, redirigir al dashboard
if (isLoggedIn()) {
    redirect('index.php');
}

// ========================================
// PROCESAMIENTO DEL FORMULARIO DE LOGIN
// ========================================
$error = ''; // Variable para almacenar mensajes de error

// Verificar si se envió el formulario (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar los datos del formulario
    $usuario = sanitizeInput($_POST['usuario'] ?? ''); // Usuario o email
    $password = $_POST['password'] ?? ''; // Contraseña (no sanitizar para verificación)
    
    // Validación básica: verificar que ambos campos estén completos
    if (empty($usuario) || empty($password)) {
        $error = 'Usuario y contraseña son requeridos';
    } else {
        // ========================================
        // VERIFICACIÓN DE CREDENCIALES
        // ========================================
        try {
            $db = getDB(); // Obtener conexión a la base de datos
            
            // Consulta SQL: buscar usuario por nombre de usuario O email
            // Esto permite iniciar sesión con cualquiera de los dos
            $stmt = $db->prepare('SELECT id, usuario, password, nombre, email FROM usuarios WHERE usuario = ? OR email = ?');
            $stmt->execute([$usuario, $usuario]); // Ejecutar con el mismo valor para ambos campos
            
            $user = $stmt->fetch(); // Obtener resultado
            
            // Verificar si el usuario existe y la contraseña es correcta
            if ($user && password_verify($password, $user['password'])) {
                // ========================================
                // LOGIN EXITOSO - CREAR SESIÓN
                // ========================================
                // Guardar información del usuario en la sesión
                $_SESSION['user_id'] = $user['id'];        // ID único del usuario
                $_SESSION['usuario'] = $user['usuario'];   // Nombre de usuario
                $_SESSION['nombre'] = $user['nombre'];     // Nombre real
                
                // Redirigir al dashboard con mensaje de bienvenida
                redirect('index.php', 'Bienvenido al panel de administración', 'success');
            } else {
                // Credenciales incorrectas
                $error = 'Credenciales inválidas';
            }
        } catch (Exception $e) {
            // Error en la base de datos
            $error = 'Error interno del servidor';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin</title>
    
    <!-- ========================================
         ENLACES A RECURSOS EXTERNOS
         ======================================== -->
    <!-- Bootstrap 5 - Framework CSS para diseño responsive -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS personalizado del panel de administración -->
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>

<!-- ========================================
     CUERPO DE LA PÁGINA
     ======================================== -->
<!-- Clases para centrar el formulario vertical y horizontalmente -->
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <!-- ========================================
                     TARJETA DEL FORMULARIO DE LOGIN
                     ======================================== -->
                <div class="card shadow-lg mt-5">
                    <div class="card-body p-4">
                        <!-- Título del formulario -->
                        <h3 class="mb-4 text-center">Panel de Administración</h3>
                        
                        <!-- ========================================
                             MENSAJES DE ERROR
                             ======================================== -->
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- ========================================
                             FORMULARIO DE LOGIN
                             ======================================== -->
                        <form method="POST" autocomplete="off">
                            <!-- Campo de usuario/email -->
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario o Email</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="usuario" 
                                       name="usuario" 
                                       required 
                                       autofocus>
                            </div>
                            
                            <!-- Campo de contraseña -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       required>
                            </div>
                            
                            <!-- Botón de envío -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                            </div>
                        </form>
                        
                        <!-- ========================================
                             ENLACE PARA VOLVER AL INICIO
                             ======================================== -->
                        <div class="mt-4 text-center">
                            <a href="../index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-house"></i> Volver al Inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 