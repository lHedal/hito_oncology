@echo off
REM =====================================================================
REM PREPARAR PROYECTO PARA LINUX - SCRIPT PARA WINDOWS
REM =====================================================================
REM Este script prepara todo el proyecto desde Windows para subirlo a Linux
REM Ejecutar desde el directorio del proyecto en Windows
REM =====================================================================

echo.
echo ====================================================================
echo   PREPARACION COMPLETA DEL PROYECTO ONCOLOGICO PARA LINUX
echo ====================================================================
echo.

REM Verificar que estamos en el directorio correcto
if not exist "index.php" (
    echo ERROR: No se encuentra index.php
    echo Ejecuta este script desde el directorio raiz del proyecto
    pause
    exit /b 1
)

echo [1/7] Creando archivo de configuracion Linux...
(
echo PROJECT_NAME=hito_oncology
echo PROJECT_DIR=/var/www/html/hito_oncology
echo DB_NAME=oncology_database
echo DB_USER=oncology_user
echo DB_PASS=oncology_secure_password_2024
echo MYSQL_ROOT_PASS=root_password_2024
echo DOMAIN_NAME=mi-clinica-oncologia.local
echo WEBHOOK_SECRET=oncology_webhook_secret_2024
) > server.config

echo [2/7] Creando script de copia para Linux...
(
echo #!/bin/bash
echo echo "Copiando archivos del proyecto..."
echo sudo mkdir -p /var/www/html/hito_oncology
echo sudo cp -r . /var/www/html/hito_oncology/
echo sudo chown -R www-data:www-data /var/www/html/hito_oncology
echo sudo chmod -R 755 /var/www/html/hito_oncology
echo echo "Archivos copiados exitosamente"
) > copiar_archivos.sh

echo [3/7] Creando configuracion de Database.php para Linux...
(
echo ^<?php
echo class Database {
echo     private static $connection = null;
echo.    
echo     public static function getConnection^(^) {
echo         if ^(self::$connection === null^) {
echo             try {
echo                 self::$connection = new PDO^(
echo                     "mysql:host=localhost;dbname=oncology_database;charset=utf8mb4",
echo                     "oncology_user",
echo                     "oncology_secure_password_2024",
echo                     [
echo                         PDO::ATTR_ERRMODE =^> PDO::ERRMODE_EXCEPTION,
echo                         PDO::ATTR_DEFAULT_FETCH_MODE =^> PDO::FETCH_ASSOC,
echo                         PDO::MYSQL_ATTR_INIT_COMMAND =^> "SET NAMES utf8mb4"
echo                     ]
echo                 ^);
echo             } catch ^(PDOException $e^) {
echo                 die^("Error de conexion: " . $e-^>getMessage^(^)^);
echo             }
echo         }
echo         return self::$connection;
echo     }
echo }
echo ?^>
) > Database_linux.php

echo [4/7] Creando .htaccess optimizado...
(
echo RewriteEngine On
echo RewriteCond %%{REQUEST_FILENAME} !-f
echo RewriteCond %%{REQUEST_FILENAME} !-d
echo RewriteRule ^^(.*)$ index.php [QSA,L]
echo.
echo ^# Seguridad
echo Header always set X-Content-Type-Options nosniff
echo Header always set X-Frame-Options DENY
echo Header always set X-XSS-Protection "1; mode=block"
echo.
echo ^# Cache para recursos estaticos
echo ^<FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg)$"^>
echo     ExpiresActive On
echo     ExpiresDefault "access plus 1 month"
echo ^</FilesMatch^>
) > .htaccess

echo [5/7] Creando comando de instalacion en una linea...
(
echo #!/bin/bash
echo ^# INSTALACION EN UNA SOLA LINEA
echo ^# Copiar y pegar este comando completo en la terminal de Linux:
echo.
echo curl -fsSL -o install.sh https://raw.githubusercontent.com/hotveryhard/hito-oncology/main/instalacion_completa_automatica.sh ^&^& chmod +x install.sh ^&^& ./install.sh
echo.
echo ^# O si no tienes GitHub aun:
echo ^# bash instalacion_completa_automatica.sh
) > COMANDO_INSTALACION_UNA_LINEA.txt

echo [6/7] Creando instrucciones completas...
(
echo ====================================================================
echo   INSTRUCCIONES DE INSTALACION ULTRA RAPIDA EN LINUX
echo ====================================================================
echo.
echo OPCION 1 - INSTALACION AUTOMATICA ^(RECOMENDADA^):
echo ------------------------------------------------
echo 1. Copia TODA esta carpeta a Linux ^(USB, FTP, etc^)
echo 2. Abre terminal en Linux y navega a la carpeta
echo 3. Ejecuta: chmod +x instalacion_completa_automatica.sh
echo 4. Ejecuta: ./instalacion_completa_automatica.sh
echo 5. Espera 10-15 minutos y listo!
echo.
echo OPCION 2 - INSTALACION ULTRA RAPIDA:
echo ----------------------------------
echo 1. Copia la carpeta a Linux
echo 2. Ejecuta: bash install_ultra_rapido.sh
echo 3. Listo en 5 minutos!
echo.
echo OPCION 3 - UNA SOLA LINEA ^(desde GitHub^):
echo ----------------------------------------
echo Ejecuta este comando completo:
echo curl -fsSL -o install.sh https://raw.githubusercontent.com/hotveryhard/hito-oncology/main/instalacion_completa_automatica.sh ^&^& chmod +x install.sh ^&^& ./install.sh
echo.
echo ACCESO AL SISTEMA:
echo -----------------
echo - URL Local: http://localhost/hito_oncology/
echo - Usuario: admin
echo - Password: admin
echo.
echo EXPONER A INTERNET:
echo ------------------
echo 1. Instalar ngrok: sudo apt install snapd ^&^& sudo snap install ngrok
echo 2. Ejecutar: ngrok http 80
echo 3. Usar la URL que te da ngrok
echo.
echo COMANDOS UTILES DESPUES DE INSTALAR:
echo -----------------------------------
echo - Iniciar sistema: oncology-start
echo - Detener sistema: oncology-stop  
echo - Ver estado: oncology-status
echo - Ver logs: tail -f /var/log/apache2/hito_oncology_error.log
echo.
echo ====================================================================
echo   SI TIENES PROBLEMAS, EJECUTA: bash install_ultra_rapido.sh
echo ====================================================================
) > INSTRUCCIONES_INSTALACION_LINUX.txt

echo [7/7] Creando paquete ZIP para transferir...
if exist "proyecto_oncologico_linux.zip" del "proyecto_oncologico_linux.zip"

REM Usar PowerShell para crear ZIP si está disponible
powershell -command "Compress-Archive -Path '.\*' -DestinationPath 'proyecto_oncologico_linux.zip' -Force" 2>nul

if exist "proyecto_oncologico_linux.zip" (
    echo ZIP creado exitosamente: proyecto_oncologico_linux.zip
) else (
    echo No se pudo crear ZIP automaticamente
    echo Crea manualmente un ZIP con todos los archivos
)

echo.
echo ====================================================================
echo                        PREPARACION COMPLETADA
echo ====================================================================
echo.
echo Archivos creados:
echo   - instalacion_completa_automatica.sh  ^(Instalador principal^)
echo   - install_ultra_rapido.sh             ^(Instalador rapido^)
echo   - INSTRUCCIONES_INSTALACION_LINUX.txt ^(Guia completa^)
echo   - COMANDO_INSTALACION_UNA_LINEA.txt   ^(Comando directo^)
echo   - Database_linux.php                  ^(Config DB para Linux^)
echo   - .htaccess                          ^(Configuracion Apache^)
echo   - server.config                      ^(Variables del servidor^)
echo   - proyecto_oncologico_linux.zip      ^(Paquete completo^)
echo.
echo PROXIMOS PASOS EN LINUX:
echo.
echo   1. Transfiere esta carpeta completa a Linux
echo   2. Abre terminal y ejecuta: chmod +x instalacion_completa_automatica.sh
echo   3. Ejecuta: ./instalacion_completa_automatica.sh
echo   4. Espera 10-15 minutos y tu sistema estara listo!
echo.
echo   O para instalacion ultra rapida:
echo   bash install_ultra_rapido.sh
echo.
echo ====================================================================
echo                    TODO LISTO PARA LINUX!
echo ====================================================================
echo.
pause
