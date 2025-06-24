<?php
// ========================================
// DATABASE.PHP - CONFIGURACIÓN DE BASE DE DATOS
// ========================================
// Este archivo maneja la conexión segura a la base de datos MySQL
// Utiliza variables de entorno para mayor seguridad

// ========================================
// CARGA DE VARIABLES DE ENTORNO
// ========================================
// Busca un archivo .env en la raíz del proyecto para cargar configuraciones
// Esto permite tener diferentes configuraciones para desarrollo y producción

// Verificar si existe el archivo .env
if (file_exists(__DIR__ . '/../.env')) {
    // Leer todas las líneas del archivo .env
    $lines = file(__DIR__ . '/../.env');
    
    // Procesar cada línea del archivo
    foreach ($lines as $line) {
        // Ignorar líneas que empiecen con # (comentarios) o estén vacías
        if (strpos(trim($line), '#') === 0 || trim($line) === '') continue;
        
        // Establecer la variable de entorno
        putenv(trim($line));
    }
}

// ========================================
// DEFINICIÓN DE CONSTANTES DE CONEXIÓN
// ========================================
// Obtener valores de las variables de entorno o usar valores por defecto
// Esto permite flexibilidad entre diferentes entornos

$DB_HOST = getenv('DB_HOST') ?: 'localhost'; // Host de la base de datos
$DB_NAME = getenv('DB_NAME') ?: 'john_alvarez_db2'; // Nombre de la base de datos
$DB_USER = getenv('DB_USER') ?: 'john_alvarez'; // Usuario de la base de datos
$DB_PASS = getenv('DB_PASS') ?: 'john_alvarez2025'; // Contraseña de la base de datos
$DB_CHARSET = getenv('DB_CHARSET') ?: 'utf8mb4'; // Charset para soporte completo de Unicode

/**
 * FUNCIÓN PRINCIPAL DE CONEXIÓN
 * =============================
 * Crea y retorna una conexión PDO a la base de datos
 * Utiliza el patrón Singleton para evitar múltiples conexiones
 * 
 * @return PDO - Objeto de conexión a la base de datos
 * @throws PDOException - Si hay error en la conexión
 */
function getDB() {
    // Variables globales para acceder a las constantes de conexión
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS, $DB_CHARSET;
    
    // Variable estática para mantener la conexión (patrón Singleton)
    static $pdo;
    
    // Si ya existe una conexión, retornarla
    if ($pdo) return $pdo;
    
    // Construir la cadena de conexión (DSN - Data Source Name)
    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
    
    try {
        // Crear nueva conexión PDO con configuraciones de seguridad
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
            // Configurar PDO para lanzar excepciones en caso de error
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            
            // Configurar el modo de fetch por defecto como array asociativo
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            
            // Deshabilitar la emulación de prepared statements para mayor seguridad
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        
        return $pdo;
    } catch (PDOException $e) {
        // Si hay error en la conexión, mostrar mensaje y terminar
        die('Error de conexión a la base de datos: ' . $e->getMessage());
    }
} 