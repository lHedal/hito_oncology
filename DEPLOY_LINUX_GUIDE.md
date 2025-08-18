# 🚀 Guía de Despliegue - Sistema Oncológico en Linux Mint

## 📋 Índice
1. [Preparación del Servidor Linux](#preparación-del-servidor-linux)
2. [Instalación de Dependencias](#instalación-de-dependencias)
3. [Configuración del Proyecto](#configuración-del-proyecto)
4. [Configuración de Base de Datos](#configuración-de-base-de-datos)
5. [Configuración del Servidor Web](#configuración-del-servidor-web)
6. [Exposición a Internet](#exposición-a-internet)
7. [Automatización con GitHub](#automatización-con-github)
8. [SSL y Seguridad](#ssl-y-seguridad)
9. [Monitoreo y Mantenimiento](#monitoreo-y-mantenimiento)

---

## 🐧 Preparación del Servidor Linux

### 1. Actualizar el Sistema
```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Instalar herramientas esenciales
```bash
sudo apt install -y curl wget git unzip software-properties-common apt-transport-https ca-certificates gnupg lsb-release
```

---

## 🔧 Instalación de Dependencias

### 1. Instalar Apache2
```bash
sudo apt install -y apache2
sudo systemctl enable apache2
sudo systemctl start apache2
```

### 2. Instalar PHP 8.2
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd php8.2-curl php8.2-mbstring php8.2-zip php8.2-intl php8.2-bcmath
```

### 3. Instalar MySQL/MariaDB
```bash
sudo apt install -y mariadb-server mariadb-client
sudo systemctl enable mariadb
sudo systemctl start mariadb
sudo mysql_secure_installation
```

### 4. Instalar Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 5. Configurar PHP para Apache
```bash
sudo a2enmod php8.2
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## 📁 Configuración del Proyecto

### 1. Crear directorio del proyecto
```bash
sudo mkdir -p /var/www/html/hito_oncology
sudo chown -R $USER:$USER /var/www/html/hito_oncology
sudo chmod -R 755 /var/www/html/hito_oncology
```

### 2. Subir archivos del proyecto
Usar AnyDesk para copiar los archivos de `c:\xampp\htdocs\hito_oncology\` a `/var/www/html/hito_oncology/`

### 3. Configurar permisos
```bash
sudo chown -R www-data:www-data /var/www/html/hito_oncology
sudo chmod -R 755 /var/www/html/hito_oncology
```

---

## 🗄️ Configuración de Base de Datos

### 1. Crear base de datos y usuario
```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE oncology_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'oncology_user'@'localhost' IDENTIFIED BY 'oncology_secure_password_2024';
GRANT ALL PRIVILEGES ON oncology_database.* TO 'oncology_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Importar esquema
```bash
mysql -u oncology_user -p oncology_database < /var/www/html/hito_oncology/oncology_schema.sql
```

### 3. Actualizar configuración de base de datos
Editar `/var/www/html/hito_oncology/core/controller/Database.php`:

```php
function __construct(){
    $this->user="oncology_user";
    $this->pass="oncology_secure_password_2024";
    $this->host="localhost";
    $this->ddbb="oncology_database";
}
```

---

## 🌐 Configuración del Servidor Web

### 1. Crear Virtual Host
```bash
sudo nano /etc/apache2/sites-available/oncology.conf
```

```apache
<VirtualHost *:80>
    ServerName localhost
    ServerAlias *.ngrok.io *.trycloudflare.com *.serveo.net
    DocumentRoot /var/www/html/hito_oncology
    
    <Directory /var/www/html/hito_oncology>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/oncology_error.log
    CustomLog ${APACHE_LOG_DIR}/oncology_access.log combined
</VirtualHost>
```

### 2. Activar sitio
```bash
sudo a2ensite oncology.conf
sudo a2dissite 000-default.conf
sudo systemctl restart apache2
```

### 3. Crear archivo .htaccess
```bash
nano /var/www/html/hito_oncology/.htaccess
```

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# Hide PHP version
ServerTokens Prod
Header unset X-Powered-By

# Cache control
<FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 month"
</FilesMatch>
```

---

## 🌍 Exposición a Internet

### Opción 1: Servidor Dedicado/VPS
Si tienes una IP pública fija:

#### 1. Configurar Firewall
```bash
sudo ufw enable
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw status
```

#### 2. Configurar port forwarding en el router
- Puerto 80 → IP local del servidor
- Puerto 443 → IP local del servidor

### Opción 2: Túnel con Ngrok (Desarrollo)
```bash
# Instalar ngrok
curl -s https://ngrok-agent.s3.amazonaws.com/ngrok.asc | sudo tee /etc/apt/trusted.gpg.d/ngrok.asc >/dev/null
echo "deb https://ngrok-agent.s3.amazonaws.com buster main" | sudo tee /etc/apt/sources.list.d/ngrok.list
sudo apt update && sudo apt install ngrok

# Autenticar (crear cuenta en ngrok.com)
ngrok config add-authtoken TU_TOKEN_AQUI

# Exponer puerto 80
ngrok http 80
```

### Opción 3: Cloudflare Tunnel (Recomendado)
```bash
# Instalar cloudflared
wget -q https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
sudo dpkg -i cloudflared-linux-amd64.deb

# Autenticar con Cloudflare
cloudflared tunnel login

# Crear túnel
cloudflared tunnel create hito-oncology

# Configurar túnel
mkdir -p ~/.cloudflared
nano ~/.cloudflared/config.yml
```

```yaml
tunnel: TU_TUNNEL_ID
credentials-file: /home/$USER/.cloudflared/TU_TUNNEL_ID.json

ingress:
  - hostname: tu-dominio.com
    service: http://localhost:80
  - service: http_status:404
```

```bash
# Crear registro DNS
cloudflared tunnel route dns hito-oncology tu-dominio.com

# Correr túnel como servicio
sudo cloudflared service install
```

---

## 🔄 Automatización con GitHub

### 1. Crear repositorio en GitHub
```bash
cd /var/www/html/hito_oncology
git init
git add .
git commit -m "Initial commit - Sistema Oncológico"
git branch -M main
git remote add origin https://github.com/TU_USUARIO/hito-oncology.git
git push -u origin main
```

### 2. Crear script de despliegue automático
```bash
sudo nano /usr/local/bin/deploy-oncology.sh
```

```bash
#!/bin/bash

# Script de despliegue automático
PROJECT_DIR="/var/www/html/hito_oncology"
BACKUP_DIR="/var/backups/oncology"
LOG_FILE="/var/log/oncology-deploy.log"

echo "$(date): Iniciando despliegue..." >> $LOG_FILE

# Crear backup
mkdir -p $BACKUP_DIR
mysqldump -u oncology_user -p'oncology_secure_password_2024' oncology_database > $BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).sql

# Backup de archivos
tar -czf $BACKUP_DIR/files_backup_$(date +%Y%m%d_%H%M%S).tar.gz -C /var/www/html hito_oncology

# Cambiar al directorio del proyecto
cd $PROJECT_DIR

# Hacer stash de cambios locales
git stash

# Obtener últimos cambios
git pull origin main

# Instalar/actualizar dependencias de Composer
composer install --no-dev --optimize-autoloader

# Ejecutar migraciones si existen
if [ -f "migrate_database.php" ]; then
    php migrate_database.php
fi

# Configurar permisos
sudo chown -R www-data:www-data $PROJECT_DIR
sudo chmod -R 755 $PROJECT_DIR

# Limpiar cache si existe
if [ -d "cache" ]; then
    rm -rf cache/*
fi

# Reiniciar servicios
sudo systemctl reload apache2

echo "$(date): Despliegue completado exitosamente" >> $LOG_FILE
```

### 3. Hacer ejecutable el script
```bash
sudo chmod +x /usr/local/bin/deploy-oncology.sh
```

### 4. Configurar webhook para GitHub
```bash
sudo apt install -y webhook
sudo mkdir -p /etc/webhook
sudo nano /etc/webhook/hooks.json
```

```json
[
  {
    "id": "deploy-oncology",
    "execute-command": "/usr/local/bin/deploy-oncology.sh",
    "command-working-directory": "/var/www/html/hito_oncology",
    "response-message": "Deployment started",
    "trigger-rule": {
      "match": {
        "type": "payload-hash-sha1",
        "secret": "tu_webhook_secret_aqui",
        "parameter": {
          "source": "header",
          "name": "X-Hub-Signature"
        }
      }
    }
  }
]
```

### 5. Crear servicio para webhook
```bash
sudo nano /etc/systemd/system/webhook.service
```

```ini
[Unit]
Description=Small server for creating HTTP endpoints (hooks)
Documentation=https://github.com/adnanh/webhook/
After=network.target

[Service]
Type=simple
User=webhook
Group=webhook
ExecStart=/usr/bin/webhook -hooks /etc/webhook/hooks.json -verbose -port 9000
Restart=on-failure
RestartSec=5s

[Install]
WantedBy=multi-user.target
```

### 6. Activar webhook
```bash
sudo useradd -r -s /bin/false webhook
sudo systemctl enable webhook
sudo systemctl start webhook
```

### 7. Configurar GitHub Webhook
En tu repositorio de GitHub:
1. Ve a Settings > Webhooks
2. Add webhook
3. Payload URL: `http://tu-dominio.com:9000/hooks/deploy-oncology`
4. Content type: `application/json`
5. Secret: `tu_webhook_secret_aqui`
6. Events: `Just the push event`

---

## 🔒 SSL y Seguridad

### 1. Instalar Certbot para SSL gratis
```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d tu-dominio.com -d www.tu-dominio.com
```

### 2. Renovación automática de SSL
```bash
sudo crontab -e
```
Agregar:
```cron
0 12 * * * /usr/bin/certbot renew --quiet
```

### 3. Configurar fail2ban
```bash
sudo apt install -y fail2ban
sudo nano /etc/fail2ban/jail.local
```

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[apache-auth]
enabled = true

[apache-badbots]
enabled = true

[apache-noscript]
enabled = true

[apache-overflows]
enabled = true
```

### 4. Configurar backup automático
```bash
sudo nano /usr/local/bin/backup-oncology.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/oncology/daily"
DATE=$(date +%Y%m%d)

mkdir -p $BACKUP_DIR

# Backup base de datos
mysqldump -u oncology_user -p'oncology_secure_password_2024' oncology_database | gzip > $BACKUP_DIR/oncology_db_$DATE.sql.gz

# Backup archivos
tar -czf $BACKUP_DIR/oncology_files_$DATE.tar.gz -C /var/www/html hito_oncology

# Eliminar backups antiguos (mantener 30 días)
find $BACKUP_DIR -type f -mtime +30 -delete
```

```bash
sudo chmod +x /usr/local/bin/backup-oncology.sh
sudo crontab -e
```
Agregar:
```cron
0 2 * * * /usr/local/bin/backup-oncology.sh
```

---

## 📊 Monitoreo y Mantenimiento

### 1. Instalar herramientas de monitoreo
```bash
sudo apt install -y htop iotop nethogs
```

### 2. Script de verificación del sistema
```bash
sudo nano /usr/local/bin/check-oncology.sh
```

```bash
#!/bin/bash

echo "=== Estado del Sistema Oncológico ==="
echo "Fecha: $(date)"
echo

# Verificar servicios
echo "=== Servicios ==="
systemctl is-active apache2 && echo "✅ Apache2: OK" || echo "❌ Apache2: FAILED"
systemctl is-active mariadb && echo "✅ MariaDB: OK" || echo "❌ MariaDB: FAILED"
systemctl is-active webhook && echo "✅ Webhook: OK" || echo "❌ Webhook: FAILED"

# Verificar espacio en disco
echo
echo "=== Espacio en Disco ==="
df -h /var/www/html

# Verificar conexión a la base de datos
echo
echo "=== Base de Datos ==="
mysql -u oncology_user -p'oncology_secure_password_2024' -e "SELECT COUNT(*) as total_users FROM oncology_database.user;" 2>/dev/null && echo "✅ Conexión DB: OK" || echo "❌ Conexión DB: FAILED"

# Verificar sitio web
echo
echo "=== Sitio Web ==="
curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q 200 && echo "✅ Sitio Web: OK" || echo "❌ Sitio Web: FAILED"

echo
echo "=== Verificación Completada ==="
```

### 3. Configurar alertas por email
```bash
sudo apt install -y mailutils postfix
sudo nano /usr/local/bin/alert-oncology.sh
```

```bash
#!/bin/bash

# Verificar servicios críticos
if ! systemctl is-active --quiet apache2; then
    echo "Apache2 está inactivo en $(hostname)" | mail -s "ALERTA: Servicio Apache2 caído" tu-email@dominio.com
fi

if ! systemctl is-active --quiet mariadb; then
    echo "MariaDB está inactivo en $(hostname)" | mail -s "ALERTA: Servicio MariaDB caído" tu-email@dominio.com
fi
```

---

## 🚀 Comandos de Despliegue Rápido

### Comando único para actualizar desde GitHub:
```bash
cd /var/www/html/hito_oncology && git pull && sudo systemctl reload apache2
```

### Comando para verificar estado completo:
```bash
/usr/local/bin/check-oncology.sh
```

### Comando para backup manual:
```bash
/usr/local/bin/backup-oncology.sh
```

---

## 📝 Notas Importantes

### URLs de Acceso:
- **Local**: `http://localhost/hito_oncology/`
- **Público**: `https://tu-dominio.com/`
- **Webhook**: `http://tu-dominio.com:9000/hooks/deploy-oncology`

### Credenciales por Defecto:
- **Usuario**: admin
- **Contraseña**: admin
- **Base de Datos**: oncology_user / oncology_secure_password_2024

### Archivos Importantes:
- **Configuración DB**: `/var/www/html/hito_oncology/core/controller/Database.php`
- **Logs Apache**: `/var/log/apache2/oncology_*.log`
- **Logs Despliegue**: `/var/log/oncology-deploy.log`
- **Backups**: `/var/backups/oncology/`

---

## 🆘 Solución de Problemas

### 1. Error de permisos
```bash
sudo chown -R www-data:www-data /var/www/html/hito_oncology
sudo chmod -R 755 /var/www/html/hito_oncology
```

### 2. Error de conexión a base de datos
```bash
mysql -u oncology_user -p
# Verificar credenciales en Database.php
```

### 3. Sitio no accesible
```bash
sudo systemctl status apache2
sudo apache2ctl configtest
sudo tail -f /var/log/apache2/error.log
```

### 4. SSL no funciona
```bash
sudo certbot certificates
sudo certbot renew --dry-run
```

---

**🎉 ¡Despliegue Completado!**

Tu sistema oncológico estará disponible en internet con actualizaciones automáticas desde GitHub.
