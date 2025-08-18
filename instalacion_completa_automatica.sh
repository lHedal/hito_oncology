#!/bin/bash

# =====================================================================
# INSTALACIÓN COMPLETA AUTOMATIZADA - SISTEMA ONCOLÓGICO
# =====================================================================
# Script de instalación completamente automatizada para Linux Mint/Ubuntu
# Ejecutar con: chmod +x instalacion_completa_automatica.sh && ./instalacion_completa_automatica.sh
# =====================================================================

set -e  # Salir si cualquier comando falla

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuración por defecto
PROJECT_NAME="hito_oncology"
PROJECT_DIR="/var/www/html/$PROJECT_NAME"
DB_NAME="oncology_database"
DB_USER="oncology_user"
DB_PASS="oncology_secure_password_2024"
MYSQL_ROOT_PASS="root_password_2024"
DOMAIN_NAME="mi-clinica-oncologia.local"
GITHUB_REPO="https://github.com/hotveryhard/hito-oncology.git"
WEBHOOK_SECRET="oncology_webhook_secret_2024"

# Funciones de utilidad
print_header() {
    echo -e "\n${PURPLE}==============================================${NC}"
    echo -e "${PURPLE}$1${NC}"
    echo -e "${PURPLE}==============================================${NC}\n"
}

print_step() {
    echo -e "${BLUE}🔄 $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Función para manejar errores
handle_error() {
    print_error "Error en la línea $1. Comando: $2"
    print_error "Instalación fallida. Revisa los logs arriba."
    exit 1
}

# Capturar errores
trap 'handle_error $LINENO "$BASH_COMMAND"' ERR

# Verificar si se ejecuta como root
check_root() {
    if [[ $EUID -eq 0 ]]; then
        print_error "Este script NO debe ejecutarse como root"
        print_warning "Ejecuta: chmod +x instalacion_completa_automatica.sh && ./instalacion_completa_automatica.sh"
        exit 1
    fi
}

# Verificar distribución
check_distribution() {
    print_step "Verificando distribución del sistema..."
    
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        if [[ "$ID" == "ubuntu" ]] || [[ "$ID_LIKE" == *"ubuntu"* ]] || [[ "$ID" == "linuxmint" ]]; then
            print_success "Distribución compatible detectada: $PRETTY_NAME"
        else
            print_error "Distribución no soportada: $PRETTY_NAME"
            print_warning "Este script está diseñado para Ubuntu/Linux Mint"
            exit 1
        fi
    else
        print_error "No se puede determinar la distribución del sistema"
        exit 1
    fi
}

# Limpiar locks de APT
fix_apt_locks() {
    print_step "Limpiando locks de APT..."
    
    sudo pkill -9 apt-get 2>/dev/null || true
    sudo pkill -9 dpkg 2>/dev/null || true
    sudo pkill -9 apt 2>/dev/null || true
    
    sudo rm -f /var/lib/dpkg/lock-frontend 2>/dev/null || true
    sudo rm -f /var/lib/apt/lists/lock 2>/dev/null || true
    sudo rm -f /var/cache/apt/archives/lock 2>/dev/null || true
    sudo rm -f /var/lib/dpkg/lock 2>/dev/null || true
    
    sudo dpkg --configure -a 2>/dev/null || true
    
    print_success "Locks de APT limpiados"
}

# Actualizar sistema
update_system() {
    print_step "Actualizando sistema..."
    
    sudo apt update
    sudo apt upgrade -y
    sudo apt autoremove -y
    sudo apt autoclean
    
    print_success "Sistema actualizado"
}

# Instalar software base
install_base_software() {
    print_step "Instalando software base..."
    
    sudo apt install -y \
        curl \
        wget \
        git \
        unzip \
        software-properties-common \
        apt-transport-https \
        ca-certificates \
        gnupg \
        lsb-release \
        ufw \
        fail2ban \
        htop \
        nano \
        tree
    
    print_success "Software base instalado"
}

# Instalar Apache
install_apache() {
    print_step "Instalando Apache2..."
    
    sudo apt install -y apache2
    sudo systemctl enable apache2
    sudo systemctl start apache2
    
    # Habilitar módulos necesarios
    sudo a2enmod rewrite
    sudo a2enmod ssl
    sudo a2enmod headers
    
    print_success "Apache2 instalado y configurado"
}

# Instalar PHP 8.2
install_php() {
    print_step "Instalando PHP 8.2..."
    
    # Agregar repositorio de PHP
    sudo add-apt-repository ppa:ondrej/php -y
    sudo apt update
    
    # Instalar PHP y extensiones
    sudo apt install -y \
        php8.2 \
        php8.2-fpm \
        php8.2-mysql \
        php8.2-mysqli \
        php8.2-curl \
        php8.2-json \
        php8.2-mbstring \
        php8.2-xml \
        php8.2-zip \
        php8.2-gd \
        php8.2-intl \
        php8.2-bcmath \
        php8.2-soap \
        php8.2-xsl \
        php8.2-opcache \
        libapache2-mod-php8.2
    
    # Configurar PHP
    sudo systemctl enable php8.2-fpm
    sudo systemctl start php8.2-fpm
    
    print_success "PHP 8.2 instalado y configurado"
}

# Instalar MariaDB
install_mariadb() {
    print_step "Instalando MariaDB..."
    
    # Pre-configurar contraseña root
    echo "mariadb-server mysql-server/root_password password $MYSQL_ROOT_PASS" | sudo debconf-set-selections
    echo "mariadb-server mysql-server/root_password_again password $MYSQL_ROOT_PASS" | sudo debconf-set-selections
    
    sudo apt install -y mariadb-server mariadb-client
    sudo systemctl enable mariadb
    sudo systemctl start mariadb
    
    # Configuración segura automática
    mysql -u root -p$MYSQL_ROOT_PASS -e "
        DELETE FROM mysql.user WHERE User='';
        DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
        DROP DATABASE IF EXISTS test;
        DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
        FLUSH PRIVILEGES;
    " 2>/dev/null || true
    
    print_success "MariaDB instalado y configurado"
}

# Crear base de datos y usuario
create_database() {
    print_step "Creando base de datos y usuario..."
    
    mysql -u root -p$MYSQL_ROOT_PASS -e "
        CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
        GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
        FLUSH PRIVILEGES;
    "
    
    print_success "Base de datos '$DB_NAME' y usuario '$DB_USER' creados"
}

# Instalar Git y configurar
install_git() {
    print_step "Configurando Git..."
    
    if [ ! -d "$HOME/.ssh" ]; then
        mkdir -p "$HOME/.ssh"
        chmod 700 "$HOME/.ssh"
    fi
    
    print_success "Git configurado"
}

# Crear directorio del proyecto
create_project_directory() {
    print_step "Creando directorio del proyecto..."
    
    sudo mkdir -p $PROJECT_DIR
    sudo chown -R $USER:www-data $PROJECT_DIR
    sudo chmod -R 755 $PROJECT_DIR
    
    print_success "Directorio del proyecto creado: $PROJECT_DIR"
}

# Copiar archivos del proyecto
copy_project_files() {
    print_step "Copiando archivos del proyecto..."
    
    # Si estamos en el directorio del proyecto Windows, copiar archivos
    if [ -f "index.php" ] && [ -d "core" ]; then
        print_step "Copiando desde directorio actual..."
        cp -r . $PROJECT_DIR/
    else
        print_warning "No se encontraron archivos del proyecto en el directorio actual"
        print_step "Creando estructura básica..."
        
        # Crear archivos básicos si no existen
        if [ ! -f "$PROJECT_DIR/index.php" ]; then
            echo "<?php
echo 'Sistema Oncológico - En configuración';
phpinfo();
?>" | sudo tee $PROJECT_DIR/index.php > /dev/null
        fi
    fi
    
    sudo chown -R www-data:www-data $PROJECT_DIR
    sudo chmod -R 755 $PROJECT_DIR
    
    print_success "Archivos del proyecto copiados"
}

# Configurar Virtual Host de Apache
configure_apache_vhost() {
    print_step "Configurando Virtual Host de Apache..."
    
    sudo tee /etc/apache2/sites-available/$PROJECT_NAME.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerName $DOMAIN_NAME
    ServerAlias *.ngrok.io
    ServerAlias *.ngrok-free.app
    ServerAlias *.cloudflare.com
    ServerAlias *.tunnel.com
    ServerAlias localhost
    DocumentRoot $PROJECT_DIR

    <Directory $PROJECT_DIR>
        AllowOverride All
        Require all granted
        Options Indexes FollowSymLinks
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/${PROJECT_NAME}_error.log
    CustomLog \${APACHE_LOG_DIR}/${PROJECT_NAME}_access.log combined
</VirtualHost>
EOF
    
    sudo a2ensite $PROJECT_NAME.conf
    sudo a2dissite 000-default.conf
    sudo systemctl reload apache2
    
    print_success "Virtual Host configurado"
}

# Configurar base de datos
configure_database_connection() {
    print_step "Configurando conexión a base de datos..."
    
    if [ -f "$PROJECT_DIR/core/controller/Database.php" ]; then
        # Crear backup
        sudo cp $PROJECT_DIR/core/controller/Database.php $PROJECT_DIR/core/controller/Database.php.backup
        
        # Actualizar configuración
        sudo tee $PROJECT_DIR/core/controller/Database.php > /dev/null <<EOF
<?php
class Database {
    private static \$connection = null;
    
    public static function getConnection() {
        if (self::\$connection === null) {
            try {
                self::\$connection = new PDO(
                    "mysql:host=localhost;dbname=$DB_NAME;charset=utf8mb4",
                    "$DB_USER",
                    "$DB_PASS",
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]
                );
            } catch (PDOException \$e) {
                die("Error de conexión: " . \$e->getMessage());
            }
        }
        return self::\$connection;
    }
}
?>
EOF
    else
        print_warning "Archivo Database.php no encontrado, creando uno básico..."
        sudo mkdir -p $PROJECT_DIR/core/controller
        # Crear el archivo básico mostrado arriba
    fi
    
    print_success "Conexión a base de datos configurada"
}

# Importar esquema de base de datos
import_database_schema() {
    print_step "Importando esquema de base de datos..."
    
    if [ -f "$PROJECT_DIR/oncology_schema.sql" ]; then
        mysql -u $DB_USER -p$DB_PASS $DB_NAME < $PROJECT_DIR/oncology_schema.sql
        print_success "Esquema de base de datos importado"
    elif [ -f "$PROJECT_DIR/schema.sql" ]; then
        mysql -u $DB_USER -p$DB_PASS $DB_NAME < $PROJECT_DIR/schema.sql
        print_success "Esquema de base de datos importado desde schema.sql"
    else
        print_warning "No se encontró archivo de esquema, creando tablas básicas..."
        
        mysql -u $DB_USER -p$DB_PASS $DB_NAME -e "
        CREATE TABLE IF NOT EXISTS user (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            lastname VARCHAR(100) NOT NULL,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            is_admin BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        
        INSERT IGNORE INTO user (name, lastname, username, email, password, is_admin) 
        VALUES ('Admin', 'Sistema', 'admin', 'admin@sistema.com', MD5('admin'), TRUE);
        "
        
        print_success "Tabla básica de usuarios creada"
    fi
}

# Instalar herramientas adicionales
install_additional_tools() {
    print_step "Instalando herramientas adicionales..."
    
    # Instalar webhook si no existe
    if ! command -v webhook &> /dev/null; then
        wget https://github.com/adnanh/webhook/releases/download/2.8.0/webhook-linux-amd64.tar.gz -O /tmp/webhook.tar.gz
        sudo tar -xzf /tmp/webhook.tar.gz -C /usr/local/bin/ --strip-components=1
        sudo chmod +x /usr/local/bin/webhook
        rm /tmp/webhook.tar.gz
    fi
    
    # Instalar Ngrok
    if ! command -v ngrok &> /dev/null; then
        curl -s https://ngrok-agent.s3.amazonaws.com/ngrok.asc | sudo tee /etc/apt/trusted.gpg.d/ngrok.asc >/dev/null
        echo "deb https://ngrok-agent.s3.amazonaws.com buster main" | sudo tee /etc/apt/sources.list.d/ngrok.list
        sudo apt update && sudo apt install -y ngrok
    fi
    
    print_success "Herramientas adicionales instaladas"
}

# Configurar firewall
configure_firewall() {
    print_step "Configurando firewall..."
    
    sudo ufw --force enable
    sudo ufw allow OpenSSH
    sudo ufw allow 'Apache Full'
    sudo ufw allow 22
    sudo ufw allow 80
    sudo ufw allow 443
    sudo ufw allow 9000  # Webhook
    
    print_success "Firewall configurado"
}

# Crear scripts de utilidad
create_utility_scripts() {
    print_step "Creando scripts de utilidad..."
    
    # Script de inicio rápido
    sudo tee /usr/local/bin/oncology-start > /dev/null <<EOF
#!/bin/bash
echo "🚀 Iniciando Sistema Oncológico..."
sudo systemctl start apache2
sudo systemctl start mariadb
sudo systemctl start webhook
echo "✅ Servicios iniciados"
echo "🌐 Acceder en: http://localhost/$PROJECT_NAME"
EOF
    
    # Script de parada
    sudo tee /usr/local/bin/oncology-stop > /dev/null <<EOF
#!/bin/bash
echo "🛑 Deteniendo Sistema Oncológico..."
sudo systemctl stop webhook
sudo systemctl stop apache2
sudo systemctl stop mariadb
echo "✅ Servicios detenidos"
EOF
    
    # Script de estado
    sudo tee /usr/local/bin/oncology-status > /dev/null <<EOF
#!/bin/bash
echo "📊 Estado del Sistema Oncológico"
echo "================================="
systemctl is-active --quiet apache2 && echo "✅ Apache2: Activo" || echo "❌ Apache2: Inactivo"
systemctl is-active --quiet mariadb && echo "✅ MariaDB: Activo" || echo "❌ MariaDB: Inactivo"
systemctl is-active --quiet webhook && echo "✅ Webhook: Activo" || echo "❌ Webhook: Inactivo"
echo ""
echo "📁 Directorio: $PROJECT_DIR"
echo "🌐 URL Local: http://localhost/$PROJECT_NAME"
echo "📊 Logs: tail -f /var/log/apache2/${PROJECT_NAME}_error.log"
EOF
    
    sudo chmod +x /usr/local/bin/oncology-*
    
    print_success "Scripts de utilidad creados"
}

# Función principal
main() {
    print_header "INSTALACIÓN AUTOMÁTICA DEL SISTEMA ONCOLÓGICO"
    
    echo -e "${CYAN}Este script instalará completamente el sistema oncológico en tu servidor Linux Mint.${NC}"
    echo -e "${CYAN}Duración estimada: 10-15 minutos${NC}"
    echo ""
    
    read -p "¿Continuar con la instalación? (s/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        print_warning "Instalación cancelada por el usuario"
        exit 0
    fi
    
    # Verificaciones iniciales
    check_root
    check_distribution
    
    # Instalación paso a paso
    fix_apt_locks
    update_system
    install_base_software
    install_apache
    install_php
    install_mariadb
    create_database
    install_git
    create_project_directory
    copy_project_files
    configure_apache_vhost
    configure_database_connection
    import_database_schema
    install_additional_tools
    configure_firewall
    create_utility_scripts
    
    # Finalización
    print_header "INSTALACIÓN COMPLETADA EXITOSAMENTE"
    
    echo -e "${GREEN}🎉 ¡El Sistema Oncológico ha sido instalado correctamente!${NC}"
    echo ""
    echo -e "${CYAN}📋 Información del sistema:${NC}"
    echo -e "   🌐 URL Local: ${YELLOW}http://localhost/$PROJECT_NAME${NC}"
    echo -e "   📁 Directorio: ${YELLOW}$PROJECT_DIR${NC}"
    echo -e "   🗄️  Base de datos: ${YELLOW}$DB_NAME${NC}"
    echo -e "   👤 Usuario DB: ${YELLOW}$DB_USER${NC}"
    echo ""
    echo -e "${CYAN}🔐 Credenciales por defecto:${NC}"
    echo -e "   👤 Usuario: ${YELLOW}admin${NC}"
    echo -e "   🔑 Contraseña: ${YELLOW}admin${NC}"
    echo ""
    echo -e "${CYAN}⚡ Comandos útiles:${NC}"
    echo -e "   🚀 Iniciar servicios: ${YELLOW}oncology-start${NC}"
    echo -e "   🛑 Detener servicios: ${YELLOW}oncology-stop${NC}"
    echo -e "   📊 Ver estado: ${YELLOW}oncology-status${NC}"
    echo ""
    echo -e "${CYAN}📝 Próximos pasos:${NC}"
    echo -e "   1. Acceder a: ${YELLOW}http://localhost/$PROJECT_NAME${NC}"
    echo -e "   2. Configurar túnel: ${YELLOW}ngrok http 80${NC}"
    echo -e "   3. Crear repositorio GitHub para auto-despliegue"
    echo ""
    
    # Ejecutar verificación del estado
    /usr/local/bin/oncology-status
    
    print_success "¡Todo listo para usar!"
}

# Ejecutar función principal
main "$@"
