#!/bin/bash

# Script de instalación automática para Linux Mint
# Sistema de Gestión Oncológica

echo "🏥 Instalando Sistema de Gestión Oncológica..."
echo "============================================="

# Verificar si se ejecuta como root
if [ "$EUID" -eq 0 ]; then
    echo "❌ No ejecutes este script como root"
    exit 1
fi

# Variables de configuración
PROJECT_NAME="hito_oncology"
PROJECT_DIR="/var/www/html/$PROJECT_NAME"
DB_NAME="oncology_database"
DB_USER="oncology_user"
DB_PASS="oncology_secure_password_2024"

echo "📦 Actualizando sistema..."

# Función para manejar problemas de APT locks
fix_apt_locks() {
    echo "🔧 Solucionando problemas de APT locks..."
    sudo killall apt apt-get dpkg 2>/dev/null || true
    sudo rm -f /var/lib/dpkg/lock-frontend
    sudo rm -f /var/lib/dpkg/lock
    sudo rm -f /var/cache/apt/archives/lock
    sudo dpkg --configure -a
    sleep 2
}

# Intentar actualización con manejo de errores
if ! sudo apt update; then
    echo "⚠️  Problema con APT, intentando solucionar..."
    fix_apt_locks
    sudo apt update
fi

echo "⬆️ Actualizando paquetes existentes..."
sudo apt upgrade -y

echo "🔧 Instalando dependencias básicas..."
# Instalar paquetes uno por uno para mejor manejo de errores
packages="apache2 mariadb-server mariadb-client curl wget git unzip software-properties-common apt-transport-https ca-certificates gnupg lsb-release"

for package in $packages; do
    echo "📦 Instalando $package..."
    if ! sudo apt install -y "$package"; then
        echo "⚠️  Error instalando $package, intentando solucionar APT..."
        fix_apt_locks
        sudo apt install -y "$package"
    fi
done

echo "🐘 Instalando PHP 8.2..."
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd php8.2-curl php8.2-mbstring php8.2-zip php8.2-intl php8.2-bcmath

echo "🎵 Configurando Apache..."
sudo a2enmod php8.2
sudo a2enmod rewrite
sudo systemctl enable apache2
sudo systemctl enable mariadb
sudo systemctl start apache2
sudo systemctl start mariadb

echo "📂 Creando directorio del proyecto..."
sudo mkdir -p $PROJECT_DIR
sudo chown -R $USER:$USER $PROJECT_DIR

echo "🗄️ Configurando base de datos..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
sudo mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

echo "🌐 Configurando Virtual Host..."
sudo tee /etc/apache2/sites-available/oncology.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot $PROJECT_DIR
    
    <Directory $PROJECT_DIR>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/oncology_error.log
    CustomLog \${APACHE_LOG_DIR}/oncology_access.log combined
</VirtualHost>
EOF

sudo a2ensite oncology.conf
sudo a2dissite 000-default.conf

echo "🔥 Configurando firewall..."
sudo ufw --force enable
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

echo "🎯 Instalando Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

echo "🔄 Reiniciando servicios..."
sudo systemctl restart apache2
sudo systemctl restart mariadb

echo "✅ Instalación base completada!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Copia los archivos del proyecto a: $PROJECT_DIR"
echo "2. Actualiza la configuración de base de datos en: $PROJECT_DIR/core/controller/Database.php"
echo "3. Importa el esquema de base de datos"
echo "4. Configura los permisos: sudo chown -R www-data:www-data $PROJECT_DIR"
echo "5. Accede al sistema en: http://localhost/$PROJECT_NAME/"
echo ""
echo "Credenciales de base de datos:"
echo "- Usuario: $DB_USER"
echo "- Contraseña: $DB_PASS"
echo "- Base de datos: $DB_NAME"
