#!/bin/bash
# INSTALACIÓN SÚPER RÁPIDA - UN SOLO COMANDO
# Usar: curl -fsSL https://raw.githubusercontent.com/tu-usuario/tu-repo/main/install.sh | bash
# O copiar este archivo y ejecutar: bash install_ultra_rapido.sh

# Descargar e ejecutar el instalador completo
curl -fsSL -o /tmp/instalacion_completa_automatica.sh https://raw.githubusercontent.com/hotveryhard/hito-oncology/main/instalacion_completa_automatica.sh 2>/dev/null || {
    echo "❌ No se pudo descargar desde GitHub, usando instalación local..."
    
    # Si no hay GitHub, hacer instalación local ultra rápida
    DEBIAN_FRONTEND=noninteractive
    export DEBIAN_FRONTEND
    
    # Limpiar APT
    sudo pkill -9 apt-get 2>/dev/null || true
    sudo rm -f /var/lib/dpkg/lock* /var/cache/apt/archives/lock /var/lib/apt/lists/lock 2>/dev/null || true
    sudo dpkg --configure -a
    
    # Instalar todo de una vez
    sudo apt update && sudo apt upgrade -y
    sudo add-apt-repository ppa:ondrej/php -y
    sudo apt update
    
    # Instalar stack completo
    echo "mariadb-server mysql-server/root_password password root_password_2024" | sudo debconf-set-selections
    echo "mariadb-server mysql-server/root_password_again password root_password_2024" | sudo debconf-set-selections
    
    sudo apt install -y apache2 mariadb-server php8.2 php8.2-mysql php8.2-curl php8.2-json php8.2-mbstring php8.2-xml php8.2-zip php8.2-gd libapache2-mod-php8.2 git curl wget
    
    # Configurar servicios
    sudo systemctl enable apache2 mariadb
    sudo a2enmod rewrite
    sudo systemctl restart apache2
    
    # Configurar DB
    mysql -u root -proot_password_2024 -e "CREATE DATABASE oncology_database; CREATE USER 'oncology_user'@'localhost' IDENTIFIED BY 'oncology_secure_password_2024'; GRANT ALL ON oncology_database.* TO 'oncology_user'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null || true
    
    # Configurar proyecto
    sudo mkdir -p /var/www/html/hito_oncology
    sudo chown -R $USER:www-data /var/www/html/hito_oncology
    
    # Copiar archivos si existen
    if [ -f "index.php" ]; then
        cp -r . /var/www/html/hito_oncology/
    else
        echo "<?php echo '<h1>Sistema Oncológico Instalado</h1>'; phpinfo(); ?>" | sudo tee /var/www/html/hito_oncology/index.php
    fi
    
    sudo chown -R www-data:www-data /var/www/html/hito_oncology
    
    # Configurar virtual host
    sudo tee /etc/apache2/sites-available/hito_oncology.conf > /dev/null <<'EOF'
<VirtualHost *:80>
    ServerName localhost
    ServerAlias *.ngrok.io *.ngrok-free.app
    DocumentRoot /var/www/html/hito_oncology
    <Directory /var/www/html/hito_oncology>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF
    
    sudo a2ensite hito_oncology.conf
    sudo a2dissite 000-default.conf
    sudo systemctl reload apache2
    
    echo "✅ INSTALACIÓN ULTRA RÁPIDA COMPLETADA"
    echo "🌐 Acceder en: http://localhost/hito_oncology/"
    echo "⚡ Para túnel externo: sudo apt install snapd && sudo snap install ngrok && ngrok http 80"
    
    exit 0
}

# Si se descargó correctamente, ejecutar el instalador completo
chmod +x /tmp/instalacion_completa_automatica.sh
/tmp/instalacion_completa_automatica.sh
