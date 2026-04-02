# 📚 Blog de Tutoriales

Blog de tutoriales para **Lenin Obregón Espinoza** (Ingeniero Nicaragüense). Sistema completo de publicaciones con soporte Markdown, multi-idioma, 20 temas de color y panel de administración.

---

## 🌟 Características Principales

### 📝 Sistema de Publicaciones
- Editor con soporte Markdown
- Tabla de contenidos automática
- Tiempo de lectura estimado
- Contador de visitas
- Posts relacionados por categoría
- Comentarios en cada publicación
- Botones de compartir (WhatsApp, Telegram, Facebook, Twitter)
- Botón copiar código en bloques de código

### 👥 Sistema de Usuarios
- Registro con preguntas de recuperación
- Login seguro con hash bcrypt
- Perfil editable (foto, biografía, redes sociales)
- Roles: Admin, Autor, Usuario
- Panel de usuario para crear publicaciones

### 📊 Admin Dashboard
- Estadísticas con gráficas Chart.js
  - Visitas últimos 30 días
  - Publicaciones por categoría
  - Páginas más visitadas
  - Usuarios por rol
- Gestión de usuarios
- Gestión de publicaciones
- Editor de página "Acerca de"
- Respaldos de base de datos

### 🎨 Sistema de Temas (20 Colores)
- Blanco, Azul, Azul Oscuro, Negro
- Verde, Rojo, Morado, Naranja
- Rosa, Teal, Amarillo, Cian
- Marrón, Índigo, Lima, Ámbar
- Rojo Rosa, Pizarra, Esmeralda, Cielo, Violeta

### 🌐 Multi-idioma
- Español (por defecto)
- English
- Selector de idioma en el menú

### 📧 Newsletter
- Suscripción por email
- Envío masivo desde admin
- Gestión de suscriptores

---

## 🛠️ Tecnologías Utilizadas

| Tecnología | Uso |
|------------|-----|
| PHP 7.4+ | Backend (PDO) |
| MySQL / MariaDB | Base de datos |
| HTML5, CSS3 | Frontend |
| JavaScript | Interactividad |
| Chart.js | Gráficos y visualización |
| Font Awesome 6 | Iconos profesionales |
| Parsedown | Markdown a HTML |
| Poppins / Fira Code | Tipografías |

---

## 🏗️ Arquitectura PHP POO - MVC

El blog está desarrollado con **Programación Orientada a Objetos (POO)** siguiendo el patrón **MVC (Modelo - Vista - Controlador)**.

### 🔷 Modelo (Model)
- Clases que gestionan la base de datos
- Consultas SQL preparadas con PDO
- Métodos CRUD reutilizables
- Ejemplo: `User.php`, `Post.php`, `Comment.php`

### 🔶 Vista (View)
- Plantillas HTML con código PHP embebido
- Variables pasadas desde los controladores
- Layouts reutilizables (header, footer)
- Ejemplo: `views/index.php`, `views/post.php`

### 🔷 Controlador (Controller)
- Recibe peticiones del usuario
- Coordina Modelo y Vista
- Maneja lógica de negocio
- Ejemplo: `HomeController.php`, `PostController.php`

### 🔄 Flujo de una Petición

```
Usuario → index.php (Router) → Controlador → Modelo → Vista → HTML
```

---

## 🚀 Instalación Rápida

### 🪟 Windows con XAMPP

1. **Copiar proyecto**: Coloca la carpeta en `C:\xampp\htdocs\blog_responsivo`

2. **Preparar el Servidor**: Inicia Apache y MySQL desde el Panel de Control de XAMPP

3. **Ejecutar el Instalador**: Visita `http://localhost/proyecto/blog_responsivo/instalar.php`

> ⚠️ **SEGURIDAD**: Una vez finalizada la instalación, elimina el archivo `instalar.php`

---

## 🔑 Credenciales de Acceso

| Campo | Valor |
|-------|-------|
| Admin URL | `/admin/` |
| Usuario | `admin` |
| Email | `admin@blog.com` |
| Contraseña | `blog$$` |

---

## 🧩 Clases del Core MVC

| Clase | Descripción |
|-------|-------------|
| Database | Conexión PDO Singleton a la base de datos |
| Session | Manejo seguro de sesiones PHP |
| Router | Enrutador de URLs (simplificado en index.php) |
| Controller | Clase base con métodos: view(), redirect(), json(), getBaseUrl() |
| Model | Clase base con métodos CRUD: find(), findById(), save(), update(), delete() |

---

## 📊 Estructura de la Base de Datos

| Tabla | Descripción |
|-------|-------------|
| users | Usuarios del sistema (admin, autor, user) |
| posts | Publicaciones del blog |
| comments | Comentarios en posts |
| newsletter | Suscriptores al newsletter |
| visit_logs | Registro de visitas al sitio |
| about | Página "Acerca de" (editable) |
| site_stats | Estadísticas globales del sitio |

---

## 📂 Estructura de Archivos

```
blog_responsivo/
├── index.php                  # Página principal (MVC Entry Point)
├── autoload.php               # Carga automática de clases
├── config.php                 # Configuración del sitio
├── database.sql               # Script SQL completo
├── instalar.php               # Instalador automático
├── subscribe.php              # Suscripción al newsletter
├── core/                      # Clases base del MVC
│   ├── Database.php           # Conexión PDO (Singleton)
│   ├── Session.php            # Manejo de sesiones
│   ├── Router.php             # Enrutador de URLs
│   ├── Controller.php         # Clase base controladores
│   ├── Model.php              # Clase base modelos
│   └── theme_colors.php       # Colores de los 20 temas
├── models/                    # Modelos de datos
│   ├── User.php               # Usuarios
│   ├── Post.php               # Publicaciones
│   ├── Comment.php            # Comentarios
│   ├── Newsletter.php         # Newsletter
│   ├── About.php              # Página Acerca de
│   ├── VisitLog.php           # Registro de visitas
│   └── SiteStats.php          # Estadísticas globales
├── controllers/               # Controladores
│   ├── HomeController.php     # Página principal
│   ├── PostController.php     # Vista de publicación
│   ├── AuthController.php     # Login y registro
│   ├── AboutController.php    # Página Acerca de
│   ├── ProfileController.php  # Perfil de usuario
│   └── SubscribeController.php # Newsletter
├── views/                     # Plantillas HTML
│   ├── layouts/
│   │   ├── header.php         # Header con navegación
│   │   └── footer.php         # Footer con newsletter
│   ├── index.php              # Vista principal
│   ├── post.php               # Vista de publicación
│   ├── about.php              # Vista Acerca de
│   ├── profile.php            # Vista perfil
│   ├── auth/
│   │   └── login.php          # Vista login/registro
│   └── errors/
│       └── 404.php            # Página no encontrada
├── includes/
│   ├── functions.php          # Funciones helper
│   └── functions_helpers.php  # Funciones adicionales MVC
├── languages/
│   ├── es.php                 # Traducciones Español
│   └── en.php                 # Traducciones English
├── db/                        # Respaldos de base de datos
├── uploads/                   # Imágenes subidas por usuarios
├── admin/                     # Panel de administración (Procedural)
│   ├── dashboard.php          # Panel de control principal
│   ├── index.php              # Editor de publicaciones
│   ├── users.php              # Gestión de usuarios
│   ├── about.php              # Editor de página Acerca de
│   ├── newsletter.php         # Gestión de newsletter
│   ├── backups.php            # Lista de respaldos
│   ├── config.php             # Configuración admin
│   ├── download_backup.php    # Script de descarga
│   └── login.php              # Login del panel admin
└── user/                      # Panel de usuario (Procedural)
    ├── index.php              # Panel de usuario
    └── logout.php             # Cerrar sesión
```

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT.

Copyright (c) 2026 Blog de Tutoriales - Lenin Obregón

Se concede permiso por la presente, de forma gratuita, a cualquier persona que obtenga una copia de este software y de los archivos de documentación asociados, para utilizar el software sin restricción.

---

## ✉️ Soporte y Respaldo
- **Backups**: Utiliza la opción "Respaldar DB" en el panel de administración regularmente
- **Soporte**: Contacta al administrador del sistema

---

## 📖 Guía de Instalación Detallada

### 🪟 Windows con XAMPP

1. Descarga e instala XAMPP desde [apachefriends.org](https://www.apachefriends.org)
2. Inicia Apache y MySQL desde el Panel de Control XAMPP
3. Clona o copia el proyecto a `C:\xampp\htdocs\blog_responsivo`
4. Ejecuta `http://localhost/proyecto/blog_responsivo/instalar.php`
5. Elimina `instalar.php` después de instalar

---

### 🐧 Linux (Debian/Ubuntu) con LAMP

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar LAMP
sudo apt install apache2 mariadb-server php php-mysql php-cli php-zip php-curl php-xml php-mbstring unzip git -y

# Habilitar servicios
sudo systemctl enable apache2 mariadb
sudo systemctl start mariadb

# IMPORTANTE: Configurar acceso root para PHP
sudo mysql -u root -p -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password; FLUSH PRIVILEGES;"
(presiona Enter cuando pida contraseña)
```

```bash
# Configurar MariaDB
sudo mysql -u root -p
```

```sql
CREATE DATABASE blog_tutoriales CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bloguser'@'localhost' IDENTIFIED BY 'bloguser';
GRANT ALL PRIVILEGES ON blog_tutoriales.* TO 'bloguser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Ejecutar el instalador: http://tu-servidor/blog_responsivo/instalar.php
```

---

### 📥 Importar Base de Datos

#### Opción 1: Desde el navegador
Ejecuta el instalador:
```
http://tu-servidor/blog_responsivo/instalar.php
```

#### Opción 2: Desde terminal

```bash
# Importar SQL
sudo mysql -u root -p blog_tutoriales < /var/www/html/blog_responsivo/db/blog_tutoriales.sql

# Verificar tablas
sudo mysql -u root -p -e "USE blog_tutoriales; SHOW TABLES;"
```

---

### 📂 Descargar proyecto

```bash
cd /var/www/html
sudo git clone https://github.com/leninobregon/blog_v2.git
```

---

### 🔐 Permisos

```bash
sudo chown -R www-data:www-data /var/www/html/blog_responsivo
sudo chmod -R 755 /var/www/html/blog_responsivo
sudo chmod 777 /var/www/html/blog_responsivo/uploads
sudo chmod 777 /var/www/html/blog_responsivo/db
```

---

### ⚙️ Configurar Apache

```bash
sudo nano /etc/apache2/sites-available/blog_responsivo.conf
```

```apache
<VirtualHost *:80>
    ServerName blog.local
    ServerAlias www.blog.local
    DocumentRoot /var/www/html/blog_responsivo

    <Directory /var/www/html/blog_responsivo>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/blog_error.log
    CustomLog ${APACHE_LOG_DIR}/blog_access.log combined
</VirtualHost>
```

```bash
sudo a2ensite blog_responsivo.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

---

## 🛠️ Solución de Problemas

### 📌 HTTP Error 500
Si aparece Error 500:

```bash
# 1. Verificar versión de PHP
php -v

# 2. Instalar extensiones PHP necesarias
sudo apt install php-mysql php-zip php-curl php-xml php-mbstring -y

# 3. Reiniciar Apache
sudo systemctl restart apache2

# 4. Ver logs
sudo tail -50 /var/log/apache2/error.log
```

### 📌 Error de Base de Datos
Si sale Error 500 por base de datos:

```bash
# Verificar MySQL/MariaDB
sudo systemctl status mariadb

# Si no está corriendo, iniciar
sudo systemctl start mariadb
sudo systemctl enable mariadb
```

```bash
# Crear base de datos manualmente
sudo mysql -u root -p
```

```sql
CREATE DATABASE blog_tutoriales CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

```bash
# O ejecutar el instalador en el navegador:
# http://tu-servidor/blog_responsivo/instalar.php
```

### 📌 Ver logs de errores

```bash
# Logs de Apache
sudo tail -f /var/log/apache2/error.log

# Logs específicos
sudo tail -100 /var/log/apache2/error.log | grep -i error
```

### 📌 Permisos correctos

```bash
sudo chown -R www-data:www-data /var/www/html/blog_responsivo
sudo find /var/www/html/blog_responsivo -type f -exec chmod 644 {} \;
sudo find /var/www/html/blog_responsivo -type d -exec chmod 755 {} \;
sudo chmod 777 /var/www/html/blog_responsivo/uploads
sudo chmod 777 /var/www/html/blog_responsivo/db
```

---

## 🌐 Linux (Debian/Ubuntu) con LEMP

```bash
# Instalar LEMP
sudo apt install nginx mariadb-server php-fpm php-mysql -y

# Configurar Nginx
sudo nano /etc/nginx/sites-available/blog_responsivo
```

```nginx
server {
    listen 80;
    server_name blog.local;
    root /var/www/html/blog_responsivo;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

```bash
# Habilitar sitio
sudo ln -s /etc/nginx/sites-available/blog_responsivo /etc/nginx/sites-enabled/
sudo systemctl reload nginx
```
