<?php
/**
 * Configuración de Base de Datos para Servidor Linux
 * Sistema de Gestión Oncológica
 */

class Database {
    public static $db;
    public static $con;
    
    function __construct(){
        // Configuración para servidor Linux Mint
        $this->user = "oncology_user";
        $this->pass = "oncology_secure_password_2024";
        $this->host = "localhost";
        $this->ddbb = "oncology_database";
        
        // Configuración SSL para producción (opcional)
        $this->ssl_ca = null; // Ruta al certificado CA si se usa SSL
        $this->ssl_verify = false; // Cambiar a true en producción con SSL
    }

    function connect(){
        try {
            $con = new mysqli($this->host, $this->user, $this->pass, $this->ddbb);
            
            // Verificar conexión
            if ($con->connect_error) {
                error_log("Error de conexión DB: " . $con->connect_error);
                throw new Exception("Error de conexión a la base de datos");
            }
            
            // Configurar charset
            $con->set_charset("utf8mb4");
            
            // Configurar zona horaria
            $con->query("SET time_zone = '-05:00'"); // Ajustar según tu zona horaria
            
            // Configurar modo SQL para compatibilidad
            $con->query("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            
            return $con;
            
        } catch (Exception $e) {
            error_log("Error en Database::connect() - " . $e->getMessage());
            
            // En desarrollo, mostrar error. En producción, mensaje genérico
            if (defined('DEBUG') && DEBUG) {
                die("Error de base de datos: " . $e->getMessage());
            } else {
                die("Error interno del servidor. Contacte al administrador.");
            }
        }
    }

    public static function getCon(){
        try {
            if(self::$con == null && self::$db == null){
                self::$db = new Database();
                self::$con = self::$db->connect();
            }
            return self::$con;
        } catch (Exception $e) {
            error_log("Error en Database::getCon() - " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verificar estado de la conexión
     */
    public static function isConnected(){
        try {
            $con = self::getCon();
            return ($con && $con->ping());
        } catch (Exception $e) {
            error_log("Error verificando conexión DB: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener información de la base de datos
     */
    public static function getInfo(){
        try {
            $con = self::getCon();
            if(!$con) return null;
            
            return [
                'server_info' => $con->server_info,
                'server_version' => $con->server_version,
                'client_info' => $con->client_info,
                'host_info' => $con->host_info,
                'protocol_version' => $con->protocol_version,
                'charset' => $con->character_set_name()
            ];
        } catch (Exception $e) {
            error_log("Error obteniendo info DB: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cerrar conexión explícitamente
     */
    public static function close(){
        if(self::$con){
            self::$con->close();
            self::$con = null;
            self::$db = null;
        }
    }
    
    /**
     * Verificar y reparar tablas
     */
    public static function checkTables(){
        try {
            $con = self::getCon();
            if(!$con) return false;
            
            $tables = ['user', 'category', 'pacient', 'medic', 'reservation', 
                      'oncology_chair', 'oncology_waitlist', 'chair_availability'];
            
            $existing_tables = [];
            $result = $con->query("SHOW TABLES");
            while($row = $result->fetch_array()){
                $existing_tables[] = $row[0];
            }
            
            $missing_tables = array_diff($tables, $existing_tables);
            
            return [
                'total_expected' => count($tables),
                'total_existing' => count($existing_tables),
                'missing_tables' => $missing_tables,
                'status' => empty($missing_tables) ? 'OK' : 'MISSING_TABLES'
            ];
            
        } catch (Exception $e) {
            error_log("Error verificando tablas: " . $e->getMessage());
            return ['status' => 'ERROR', 'message' => $e->getMessage()];
        }
    }
}

// Función de utilidad para logging de errores DB
function logDbError($message, $query = null) {
    $log_message = date('Y-m-d H:i:s') . " - DB ERROR: " . $message;
    if($query) {
        $log_message .= " | Query: " . $query;
    }
    error_log($log_message);
}

// Auto-verificación en desarrollo
if (defined('DEBUG') && DEBUG && php_sapi_name() !== 'cli') {
    register_shutdown_function(function(){
        if(!Database::isConnected()){
            error_log("ADVERTENCIA: Conexión a BD perdida al finalizar script");
        }
    });
}
?>
