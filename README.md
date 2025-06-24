# Project-Portafolio

Sistema de portafolio personal con CRUD completo y API REST en PHP + MySQL.

## 📋 Descripción

Portafolio personal profesional que permite mostrar proyectos de desarrollo con un sistema de administración completo. Incluye un sitio público atractivo y un panel de administración protegido para gestionar proyectos, además de una API REST para integraciones externas.

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5
- **Iconos**: Bootstrap Icons
- **Autenticación**: JWT Tokens
- **API**: RESTful con JSON

## 📁 Estructura del Proyecto

```
Project-Portafolio/
├── admin/                    # Panel de administración
│   ├── index.php            # Dashboard principal
│   ├── login.php            # Página de login
│   ├── logout.php           # Cerrar sesión
│   ├── proyectos.php        # Lista de proyectos
│   ├── agregar-proyecto.php # Crear proyecto
│   ├── editar-proyecto.php  # Editar proyecto
│   └── eliminar-proyecto.php # Eliminar proyecto
├── api/                     # API REST
│   ├── config.php           # Configuración de la API
│   ├── auth.php             # Autenticación
│   └── proyectos.php        # Endpoints de proyectos
├── assets/                  # Recursos estáticos
│   ├── css/
│   │   ├── style.css        # Estilos del sitio público
│   │   └── admin.css        # Estilos del panel admin
│   ├── js/
│   │   ├── main.js          # JavaScript del sitio público
│   │   └── admin.js         # JavaScript del panel admin
│   └── images/              # Imágenes del sitio
├── config/
│   └── database.php         # Conexión a base de datos
├── includes/
│   └── functions.php        # Funciones auxiliares
├── uploads/                 # Imágenes de proyectos
├── index.php               # Sitio público principal
├── database.sql            # Estructura de la base de datos
└── README.md              # Documentación

```

## 👨‍💼 Panel de Administración

### Acceso
- Usuario: `john`
- Contraseña: `john123`

### Funcionalidades
1. **Gestionar Proyectos**
   - Crear nuevos proyectos
   - Editar proyectos existentes
   - Eliminar proyectos
   - Subir imágenes

2. **Subida de Imágenes**
   - Formatos soportados: JPG, PNG, WEBP
   - Tamaño máximo: 5MB
   - Validación automática

## 🙏 IAs Utilizadas

- Claude: Se tu utilizó esta IA para hacer una estructura del proyecto y así poder trabajar en ella, además de su contribución para corregir errores y bugs.
- Chat GPT: Se utilizó esta IA para resolver ciertos bugs.

## 👨‍💻 Autor

**John Álvarez**
- Email: jalvarez2023@alu.uct.cl
- GitHub: [John10Alvarezz](https://github.com/John10Alvarezz)
