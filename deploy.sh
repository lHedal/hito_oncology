#!/bin/bash

# Script de despliegue automático para GitHub
# Sistema de Gestión Oncológica

PROJECT_DIR="/var/www/html/hito_oncology"
BACKUP_DIR="/var/backups/oncology"
LOG_FILE="/var/log/oncology-deploy.log"
DB_USER="oncology_user"
DB_PASS="oncology_secure_password_2024"
DB_NAME="oncology_database"

# Función de logging
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S'): $1" | tee -a $LOG_FILE
}

# Función de backup
backup() {
    log "Creando backup..."
    mkdir -p $BACKUP_DIR
    
    # Backup de base de datos
    mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).sql
    
    # Backup de archivos
    tar -czf $BACKUP_DIR/files_backup_$(date +%Y%m%d_%H%M%S).tar.gz -C /var/www/html hito_oncology
    
    # Mantener solo los últimos 10 backups
    ls -t $BACKUP_DIR/backup_*.sql | tail -n +11 | xargs rm -f
    ls -t $BACKUP_DIR/files_backup_*.tar.gz | tail -n +11 | xargs rm -f
}

# Función de rollback
rollback() {
    log "ERROR: Realizando rollback..."
    cd $PROJECT_DIR
    git reset --hard HEAD~1
    sudo systemctl reload apache2
    log "Rollback completado"
    exit 1
}

# Inicio del despliegue
log "=== INICIANDO DESPLIEGUE ==="

# Verificar servicios
if ! systemctl is-active --quiet apache2; then
    log "ERROR: Apache2 no está activo"
    exit 1
fi

if ! systemctl is-active --quiet mariadb; then
    log "ERROR: MariaDB no está activo"
    exit 1
fi

# Crear backup antes del despliegue
backup

# Cambiar al directorio del proyecto
cd $PROJECT_DIR || exit 1

# Verificar estado del repositorio
if [ -n "$(git status --porcelain)" ]; then
    log "Hay cambios locales, haciendo stash..."
    git stash push -m "Auto-stash before deployment $(date)"
fi

# Obtener commit actual para rollback
CURRENT_COMMIT=$(git rev-parse HEAD)
log "Commit actual: $CURRENT_COMMIT"

# Obtener últimos cambios
log "Obteniendo cambios desde GitHub..."
if ! git pull origin main; then
    log "ERROR: No se pudo obtener los cambios desde GitHub"
    exit 1
fi

# Verificar si hay archivos composer
if [ -f "composer.json" ]; then
    log "Actualizando dependencias de Composer..."
    composer install --no-dev --optimize-autoloader || rollback
fi

# Ejecutar migraciones si existen
if [ -f "migrate_database.php" ]; then
    log "Ejecutando migraciones de base de datos..."
    php migrate_database.php || rollback
fi

# Verificar estructura de base de datos
log "Verificando base de datos..."
php -r "
    try {
        \$pdo = new PDO('mysql:host=localhost;dbname=$DB_NAME', '$DB_USER', '$DB_PASS');
        \$tables = \$pdo->query('SHOW TABLES')->fetchAll();
        if (count(\$tables) < 5) {
            throw new Exception('Faltan tablas en la base de datos');
        }
        echo 'Base de datos OK\n';
    } catch (Exception \$e) {
        echo 'ERROR DB: ' . \$e->getMessage() . '\n';
        exit(1);
    }
" || rollback

# Configurar permisos
log "Configurando permisos..."
sudo chown -R www-data:www-data $PROJECT_DIR
sudo chmod -R 755 $PROJECT_DIR

# Limpiar cache si existe
if [ -d "$PROJECT_DIR/cache" ]; then
    log "Limpiando cache..."
    sudo rm -rf $PROJECT_DIR/cache/*
fi

if [ -d "$PROJECT_DIR/tmp" ]; then
    log "Limpiando archivos temporales..."
    sudo rm -rf $PROJECT_DIR/tmp/*
fi

# Verificar que el sitio responde
log "Verificando sitio web..."
if ! curl -f -s -o /dev/null http://localhost/hito_oncology/; then
    log "ERROR: El sitio no responde correctamente"
    rollback
fi

# Reiniciar servicios
log "Reiniciando Apache..."
sudo systemctl reload apache2

# Verificación final
sleep 2
if curl -f -s -o /dev/null http://localhost/hito_oncology/; then
    log "✅ DESPLIEGUE EXITOSO"
    
    # Enviar notificación si está configurado
    if command -v mail > /dev/null; then
        echo "Despliegue exitoso del sistema oncológico - $(date)" | mail -s "✅ Despliegue Exitoso" admin@localhost 2>/dev/null || true
    fi
else
    log "❌ ERROR: Sitio no responde después del despliegue"
    rollback
fi

log "=== DESPLIEGUE COMPLETADO ==="
