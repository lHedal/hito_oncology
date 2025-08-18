# 🏥 Sistema de Gestión Oncológica

## Descripción
Sistema especializado para la gestión integral de pacientes oncológicos, desarrollado como una solución independiente y enfocada exclusivamente en las necesidades del departamento de oncología.

## ✨ Características Principales

### 🎯 Funcionalidades Oncológicas
- **Dashboard Especializado**: Panel de control con métricas específicas de oncología
- **Gestión de Lista de Espera**: Sistema avanzado de priorización de pacientes
- **Administración de Sillones**: Control de recursos de tratamiento especializado
- **Calendario Oncológico**: Programación de sesiones de quimioterapia y tratamientos
- **Asignación Automática**: Algoritmo inteligente para optimizar recursos

### 👥 Gestión de Usuarios
- **Pacientes Oncológicos**: Registro completo con historial médico
- **Médicos Especialistas**: Gestión exclusiva de oncólogos
- **Sistema de Autenticación**: Control de acceso seguro
- **Roles Diferenciados**: Permisos específicos por tipo de usuario

## 🗄️ Base de Datos
**Nombre**: `oncology_database`

### Tablas Principales:
- `user` - Usuarios del sistema
- `category` - Categorías médicas (Oncología)
- `pacient` - Pacientes oncológicos
- `medic` - Médicos especialistas
- `reservation` - Citas y tratamientos
- `oncology_chair` - Sillones de tratamiento
- `oncology_waitlist` - Lista de espera prioritaria
- `chair_availability` - Disponibilidad de recursos
- `oncology_config` - Configuración del sistema

## 🚀 Instalación

### Prerrequisitos
- PHP 8.2 o superior
- MySQL/MariaDB
- Servidor web (Apache/XAMPP recomendado)
- Navegador web moderno

### Pasos de Instalación

1. **Colocar archivos en el servidor web**
   ```
   Copiar la carpeta 'hito_oncology' a c:\xampp\htdocs\
   ```

2. **Ejecutar migración de base de datos**
   ```
   Visitar: http://localhost/hito_oncology/migrate_database.php
   ```

3. **Verificar instalación**
   ```
   Visitar: http://localhost/hito_oncology/system_verification.php
   ```

4. **Acceder al sistema**
   ```
   URL: http://localhost/hito_oncology/
   Usuario por defecto: admin
   Contraseña por defecto: admin
   ```

## 📊 Estructura del Sistema

### Modelos Principales
- `OncologyChairData` - Gestión de sillones
- `OncologyWaitlistData` - Lista de espera
- `OncologySchedulingService` - Servicio de programación
- `PacientData` - Gestión de pacientes
- `MedicData` - Gestión de médicos (métodos oncológicos)
- `ReservationData` - Gestión de citas (métodos oncológicos)

### Vistas Especializadas
- `oncologydashboard-view` - Dashboard principal
- `oncologysystem-view` - Estado general
- `oncologywaitlist-view` - Gestión de lista de espera
- `oncologychairs-view` - Administración de sillones
- `oncologycalendar-view` - Calendario especializado

### Acciones del Sistema
- Gestión completa de lista de espera
- Administración de sillones de tratamiento
- Asignación automática de recursos
- Verificación de disponibilidad
- Procesamiento masivo de lista de espera

## 🔧 Configuración

### Configuración de Base de Datos
Archivo: `core/controller/Database.php`
```php
$this->host = "localhost";
$this->user = "root";
$this->pass = "";
$this->ddbb = "oncology_database";
```

### Vista por Defecto
El sistema redirige automáticamente al dashboard de oncología para usuarios autenticados.

## 📱 Navegación del Sistema

### Panel Principal
- **Dashboard**: Métricas y estadísticas del día
- **Estado General**: Resumen completo del sistema

### Gestión de Pacientes
- Ver todos los pacientes
- Agregar nuevos pacientes
- Editar información existente

### Gestión de Médicos
- Administrar médicos oncólogos
- Asignar especialidades
- Control de accesos

### Sistema Oncológico
- **Lista de Espera**: Priorización inteligente
- **Sillones**: Gestión de recursos
- **Calendario**: Programación de tratamientos

## 🔒 Seguridad

- Autenticación de usuarios requerida
- Sesiones seguras
- Validación de datos
- Prevención de inyección SQL
- Control de acceso basado en roles

## 📈 Optimizaciones

- **DataTables**: Tablas responsivas y filtrable
- **Consultas Optimizadas**: Índices en tablas críticas
- **JavaScript Mínimo**: Prevención de conflictos
- **Renderizado Eficiente**: Carga rápida de vistas

## 🧪 Testing y Verificación

### Script de Verificación
```
URL: http://localhost/hito_oncology/system_verification.php
```

Este script verifica:
- Conexión a base de datos
- Existencia de tablas requeridas
- Funcionalidad de modelos
- Integridad de archivos
- Enlaces de navegación

## 📞 Soporte

### Enlaces Principales del Sistema
- **Dashboard**: `/?view=oncologydashboard`
- **Lista de Espera**: `/?view=oncologywaitlist`
- **Sillones**: `/?view=oncologychairs`
- **Pacientes**: `/?view=pacients`
- **Médicos**: `/?view=medics`
- **Usuarios**: `/?view=users`

## 🏆 Características Destacadas

### ✅ Sistema Limpio y Enfocado
- Sin módulos innecesarios
- Interfaz simplificada
- Navegación intuitiva

### ✅ Especialización Oncológica
- Funcionalidades específicas del área
- Terminología médica apropiada
- Flujo de trabajo optimizado

### ✅ Escalabilidad
- Arquitectura modular
- Base de datos normalizada
- Fácil mantenimiento

### ✅ Usabilidad
- Interfaz responsiva
- Controles intuitivos
- Feedback inmediato

---

**🎉 Sistema de Oncología v2.0 - Especializado, Eficiente y Completo**

*Desarrollado específicamente para las necesidades del departamento de oncología con enfoque en la eficiencia y facilidad de uso.*
