-- Blog Tutoriales Database
CREATE DATABASE IF NOT EXISTS blog_tutoriales;
USE blog_tutoriales;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'author', 'user') DEFAULT 'user',
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    avatar VARCHAR(255),
    bio TEXT,
    facebook VARCHAR(255),
    twitter VARCHAR(255),
    telegram VARCHAR(255),
    instagram VARCHAR(255),
    youtube VARCHAR(255),
    linkedin VARCHAR(255),
    website VARCHAR(255),
    recovery_question VARCHAR(255),
    recovery_answer VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de publicaciones
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    video VARCHAR(255),
    author_id INT,
    views INT DEFAULT 0,
    tags VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de comentarios
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de Newsletter
CREATE TABLE IF NOT EXISTS newsletter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100),
    active TINYINT(1) DEFAULT 1,
    token VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de logs de visitas
CREATE TABLE IF NOT EXISTS visit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page VARCHAR(255),
    ip VARCHAR(45),
    user_agent TEXT,
    referer VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Acerca de (editable desde admin)
CREATE TABLE IF NOT EXISTS about (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) DEFAULT 'Acerca de Mí',
    subtitle VARCHAR(255) DEFAULT 'Ingeniero en Computación',
    description TEXT,
    experience TEXT,
    goals TEXT,
    photo VARCHAR(255),
    youtube_url VARCHAR(255),
    facebook_url VARCHAR(255),
    twitter_url VARCHAR(255),
    telegram_url VARCHAR(255),
    email VARCHAR(100),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar datos iniciales de Acerca de
INSERT INTO about (id, title, subtitle, description, experience, goals, youtube_url, email) VALUES 
(1, 'Acerca de Mí', 'Ingeniero en Computación', 
'Lenin Obregón Espinoza es Ingeniero en Computación de nacionalidad Nicaragüense de la tierra de lagos y volcanes montañas con pinares. Este canal es para mi memoria de aprendizaje y está orientado para aquellas personas que están iniciando el aprendizaje de infraestructuras, tengo mucha experiencia en el campo de Networking, Administración de Servidores en Entornos Linux y Windows, Monitoreo de Equipos de Comunicación entre otros.',
'Networking|Administración de Servidores en Entornos Linux y Windows|Monitoreo de Equipos de Comunicación',
'Promover el uso de tecnologías de información|Software libre|Área de Campus Networking|Ayudar a iniciados en el mundo IT',
'https://www.youtube.com/@leninobregonespinoza2160',
'lenin@ejemplo.com');

-- Tabla de visitas
CREATE TABLE IF NOT EXISTS site_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total_hits INT DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO site_stats (id, total_hits) VALUES (1, 0);

-- Insertar usuario admin (password: blog$$)
INSERT INTO users (username, email, password, role, first_name, last_name, phone, bio, recovery_question, recovery_answer) VALUES 
('admin', 'admin@blog.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin', 'Principal', '+5911234567', 'Administrador del blog', '¿Cuál es el nombre de tu primera mascota?', 'admin123');

-- Insertar autores (password: password123)
INSERT INTO users (username, email, password, role, first_name, last_name, phone, bio, recovery_question, recovery_answer) VALUES 
('juanperez', 'juan@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'author', 'Juan', 'Perez', '+5912234567', 'Desarrollador web', '¿Cuál es tu ciudad natal?', 'lapaz'),
('maria-dev', 'maria@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'author', 'Maria', 'Garcia', '+5913234567', 'Desarrolladora Python', '¿Cuál es tu comida favorita?', 'pizza');

-- Insertar usuarios normales (password: password123)
INSERT INTO users (username, email, password, role, first_name, last_name, phone, bio, recovery_question, recovery_answer) VALUES 
('carloslopez', 'carlos@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'Carlos', 'Lopez', '+5914234567', 'Sysadmin', '¿Cuál es el nombre de tu mejor amigo?', 'luis'),
('pepe123', 'pepe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'Jose', 'Ruiz', '+5915234567', 'Estudiante', '¿Cuál es el nombre de tu escuela primaria?', 'sanignacio');

-- Insertar publicaciones
INSERT INTO posts (title, category, content, author_id) VALUES 
('Bienvenido al Blog', 'General', '# Bienvenido\n\nEste es un blog de tutoriales sobre programación, Linux y seguridad.', 1),
('Introducción a Linux', 'Linux', '# Introducción a Linux\n\nLinux es un sistema operativo de código abierto.\n\n## Distribuciones\n- Ubuntu\n- Debian\n\n```bash\nls -la\ncd /home\n```', 1),
('Tutorial de Python', 'Programación', '# Tutorial de Python\n\n```python\nnombre = "Juan"\nedad = 25\n```', 2),
('Configurar SSH', 'Linux', '# Configurar SSH\n\n```bash\nsudo apt update\nsudo apt install openssh-server\nsudo systemctl start ssh\nsudo systemctl enable ssh\n```\n\n## Conectar\n```bash\nssh usuario@servidor\n```', 1),
('Comandos Servidor', 'Linux', '# Comandos básicos servidor\n\n## Archivos\n```bash\nls -la\ncd /home\nmkdir carpeta\nrm archivo\ncp origen destino\nmv origen destino\n```\n\n## Paquetes\n```bash\nsudo apt update\nsudo apt upgrade\nsudo apt install nombre\n```\n\n## Servicios\n```bash\nsudo systemctl status nginx\nsudo systemctl start nginx\nsudo systemctl stop nginx\nsudo systemctl restart nginx\n```\n\n## Usuarios\n```bash\nsudo adduser nombre\nsudo usermod -aG sudo nombre\n```', 1),
('LAMP Debian/Ubuntu', 'Linux', '# LAMP Server\n\nLAMP = Linux + Apache + MySQL + PHP\n\n```bash\n# Apache\nsudo apt install apache2 -y\n\n# MySQL\nsudo apt install mariadb-server -y\nsudo mysql_secure_installation\n\n# PHP\nsudo apt install php libapache2-mod-php php-mysql -y\n\n# Verificar\necho "<?php phpinfo(); ?>" | sudo tee /var/www/html/info.php\n```', 1),
('Firewall UFW', 'Linux', '# Firewall UFW\n\n```bash\n# Instalar\nsudo apt install ufw\n\n# Reglas\nsudo ufw allow ssh\nsudo ufw allow 80/tcp\nsudo ufw allow 443/tcp\n\n# Habilitar\nsudo ufw enable\n\n# Ver estado\nsudo ufw status\n```\n\n## Otras reglas\n```bash\nsudo ufw allow 22/tcp\nsudo ufw allow 3306/tcp  # MySQL\nsudo ufw allow 8080/tcp\n```', 1),
('Servidor NFS', 'Linux', '# Servidor NFS\n\nNFS permite compartir archivos entre servidores.\n\n## Servidor\n```bash\nsudo apt install nfs-kernel-server\nsudo mkdir -p /var/nfs/compartido\nsudo chmod 777 /var/nfs/compartido\n\n# Editar /etc/exports\n/var/nfs/compartido 192.168.1.0/24(rw,sync,no_subtree_check)\n\nsudo exportfs -a\nsudo systemctl restart nfs-kernel-server\n```\n\n## Cliente\n```bash\nsudo apt install nfs-common\nsudo mkdir -p /mnt/nfs\nsudo mount 192.168.1.100:/var/nfs/compartido /mnt/nfs\n```', 1),
('Nginx Reverse Proxy', 'Linux', '# Nginx como Proxy Inverso\n\n```bash\nsudo apt install nginx\n```\n\n## Configurar\n```nginx\nserver {\n    listen 80;\n    server_name mi-dominio.com;\n    \n    location / {\n        proxy_pass http://127.0.0.1:3000;\n        proxy_set_header Host $host;\n        proxy_set_header X-Real-IP $remote_addr;\n        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;\n    }\n}\n```\n\n## Activar\n```bash\nsudo ln -s /etc/nginx/sites-available/mi-dominio /etc/nginx/sites-enabled/\nsudo nginx -t\nsudo systemctl reload nginx\n```', 1),
('Usuario Sudo', 'Linux', '# Crear Usuario Sudo\n\n```bash\n# Crear usuario\nsudo adduser nombre_usuario\n\n# Agregar a grupo sudo\nsudo usermod -aG sudo nombre_usuario\n\n# Verificar\nsu - nombre_usuario\nsudo whoami\n# Debe mostrar: root\n```\n\n## Editar sudoers\n```bash\nsudo visudo\n# Agregar:\nnombre_usuario ALL=(ALL:ALL) ALL\n```', 1);
