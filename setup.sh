#!/bin/bash

# Script de configuración rápida post-instalación
# Sistema de Gestión Oncológica

echo "🔧 Configuración rápida del Sistema Oncológico"
echo "=============================================="

# Cargar configuración
source /var/www/html/hito_oncology/server.config 2>/dev/null || {
    echo "⚠️  Archivo de configuración no encontrado, usando valores por defecto"
    PROJECT_DIR="/var/www/html/hito_oncology"
    DB_NAME="oncology_database"
    DB_USER="oncology_user"
    DB_PASS="oncology_secure_password_2024"
}

echo "📁 Configurando permisos de archivos..."
sudo chown -R www-data:www-data $PROJECT_DIR
sudo chmod -R 755 $PROJECT_DIR
sudo chmod +x $PROJECT_DIR/deploy.sh
sudo chmod +x $PROJECT_DIR/install_server.sh

echo "🗄️ Importando esquema de base de datos..."
if [ -f "$PROJECT_DIR/oncology_schema.sql" ]; then
    mysql -u $DB_USER -p$DB_PASS $DB_NAME < $PROJECT_DIR/oncology_schema.sql
    echo "✅ Esquema importado exitosamente"
else
    echo "⚠️  Archivo oncology_schema.sql no encontrado"
fi

echo "🔄 Ejecutando migraciones..."
if [ -f "$PROJECT_DIR/migrate_database.php" ]; then
    cd $PROJECT_DIR
    php migrate_database.php
    echo "✅ Migraciones ejecutadas"
fi

echo "📋 Creando directorios necesarios..."
sudo mkdir -p /var/backups/oncology
sudo mkdir -p /var/log/oncology
sudo mkdir -p /etc/webhook

echo "🔧 Configurando logs..."
sudo touch /var/log/oncology-deploy.log
sudo chown www-data:www-data /var/log/oncology-deploy.log

echo "🌐 Configurando webhook..."
sudo cp $PROJECT_DIR/webhook-config.json /etc/webhook/hooks.json

# Crear servicio de webhook si no existe
if [ ! -f "/etc/systemd/system/webhook.service" ]; then
    echo "📋 Creando servicio webhook..."
    sudo tee /etc/systemd/system/webhook.service > /dev/null <<EOF
[Unit]
Description=Small server for creating HTTP endpoints (hooks)
Documentation=https://github.com/adnanh/webhook/
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
ExecStart=/usr/bin/webhook -hooks /etc/webhook/hooks.json -verbose -port 9000 -ip 0.0.0.0
Restart=on-failure
RestartSec=5s

[Install]
WantedBy=multi-user.target
EOF

    sudo systemctl daemon-reload
    sudo systemctl enable webhook
fi

echo "🔄 Reiniciando servicios..."
sudo systemctl restart apache2
sudo systemctl restart webhook

echo "🧪 Ejecutando verificación del sistema..."
if [ -f "$PROJECT_DIR/system_verification.php" ]; then
    cd $PROJECT_DIR
    php system_verification.php
fi

echo "✅ Configuración completada!"
echo ""
echo "📋 Información del sistema:"
echo "- Proyecto: $PROJECT_DIR"
echo "- URL Local: http://localhost/hito_oncology/"
echo "- Logs: /var/log/oncology-deploy.log"
echo "- Backups: /var/backups/oncology/"
echo "- Webhook: http://localhost:9000/hooks/deploy-oncology"
echo ""
echo "🔐 Credenciales por defecto:"
echo "- Usuario: admin"
echo "- Contraseña: admin"
echo ""
echo "⚡ Comandos útiles:"
echo "- Ver logs: tail -f /var/log/oncology-deploy.log"
echo "- Desplegar: cd $PROJECT_DIR && ./deploy.sh"
echo "- Estado servicios: systemctl status apache2 mariadb webhook"
