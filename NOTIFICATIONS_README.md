# 🔔 Sistema de Notificaciones - Oncología

## Descripción
Sistema completo de notificaciones por email para el sistema de gestión oncológica. Permite el envío automático de notificaciones a pacientes y médicos sobre citas, tratamientos y estados de lista de espera.

## 🚀 Características

### ✅ Notificaciones Automáticas
- **Citas Agendadas**: Confirmación automática cuando se agenda una cita
- **Recordatorios**: Notificación 24 horas antes de cada cita
- **Lista de Espera**: Confirmación al agregar paciente a lista de espera
- **Asignación de Citas**: Notificación cuando se asigna cita desde lista de espera
- **Bienvenida**: Email de bienvenida para nuevos pacientes registrados

### 📧 Sistema de Email
- **Templates HTML**: Plantillas responsivas y profesionales
- **Variables Dinámicas**: Personalización automática con datos del paciente/médico
- **Configuración SMTP**: Compatible con Gmail, Outlook y otros proveedores
- **Cola de Notificaciones**: Sistema de envío programado y confiable

### 🎛️ Panel de Administración
- **Historial Completo**: Registro de todas las notificaciones enviadas
- **Estadísticas**: Dashboard con métricas de envío
- **Configuración SMTP**: Panel fácil para configurar servidor de email
- **Gestión de Cola**: Visualización y control de notificaciones pendientes

## 📋 Instalación

### 1. Ejecutar Instalador
```
http://localhost/hito_oncology/notification_installer.php
```

### 2. Aplicar Esquema de Base de Datos
```
http://localhost/hito_oncology/apply_notification_schema.php
```

### 3. Configurar SMTP
1. Ir a **Notificaciones > Configuración SMTP**
2. Completar datos del servidor de email
3. Probar configuración

## ⚙️ Configuración

### Gmail (Recomendado)
```
Servidor SMTP: smtp.gmail.com
Puerto: 587
Seguridad: TLS
Usuario: su-email@gmail.com
Contraseña: [Contraseña de Aplicación]
```

**Importante**: Use una contraseña de aplicación, no su contraseña normal de Gmail.

### Otros Proveedores
- **Outlook**: smtp-mail.outlook.com:587 (TLS)
- **Yahoo**: smtp.mail.yahoo.com:587 (TLS)
- **SMTP Personalizado**: Configure según su proveedor

## 🔄 Procesamiento Automático

### Cron Job (Recomendado)
Para procesamiento automático de la cola:
```bash
# Cada 10 minutos
*/10 * * * * /usr/bin/php /ruta/completa/notification_processor.php
```

### Procesamiento Manual
También puede procesar la cola manualmente desde:
- **Notificaciones > Cola de Envíos > Procesar Cola**

## 📊 Tipos de Notificaciones

| Código | Descripción | Destinatario |
|--------|-------------|--------------|
| `appointment_scheduled` | Cita agendada | Paciente + Médico |
| `appointment_reminder` | Recordatorio 24h | Solo Paciente |
| `waitlist_added` | Agregado a lista | Paciente + Médico |
| `waitlist_assigned` | Cita asignada | Paciente + Médico |
| `patient_registered` | Nuevo paciente | Solo Paciente |
| `appointment_cancelled` | Cita cancelada | Paciente + Médico |
| `treatment_completed` | Tratamiento completado | Paciente + Médico |

## 🗂️ Estructura de Archivos

```
📁 Sistema de Notificaciones
├── 📄 notification_schema.sql          # Esquema de base de datos
├── 📄 notification_processor.php       # Procesador automático (cron)
├── 📄 notification_installer.php       # Instalador del sistema
├── 📄 apply_notification_schema.php    # Aplicador de esquema
├── 📁 core/app/model/
│   ├── 📄 NotificationData.php         # Modelos de datos
│   └── 📄 NotificationService.php      # Servicio principal
├── 📁 core/app/view/
│   ├── 📄 notifications-view.php       # Historial de notificaciones
│   ├── 📄 notificationconfig-view.php  # Configuración SMTP
│   └── 📄 notificationqueue-view.php   # Cola de notificaciones
└── 📁 core/app/action/
    ├── 📄 updatenotificationconfig-action.php
    ├── 📄 processnotificationqueue-action.php
    ├── 📄 testnotificationemail-action.php
    └── 📄 [otras acciones...]
```

## 🎨 Variables de Plantilla

Las plantillas de email soportan las siguientes variables:

| Variable | Descripción |
|----------|-------------|
| `{{patient_name}}` | Nombre completo del paciente |
| `{{medic_name}}` | Nombre completo del médico |
| `{{date}}` | Fecha de la cita (dd/mm/yyyy) |
| `{{time}}` | Hora de la cita (HH:mm) |
| `{{treatment_type}}` | Tipo de tratamiento |
| `{{chair_name}}` | Nombre del sillón asignado |
| `{{priority_level}}` | Nivel de prioridad |
| `{{email}}` | Email del destinatario |

## 🔧 API de Uso

### Enviar Notificación Inmediata
```php
NotificationService::sendNotification(
    'appointment_scheduled',
    ['email' => 'paciente@email.com', 'name' => 'Juan Pérez', 'type' => 'patient'],
    ['date' => '15/06/2025', 'time' => '14:30', 'medic_name' => 'Dr. García'],
    $reservation_id,
    'reservation'
);
```

### Programar Notificación
```php
NotificationService::scheduleNotification(
    'appointment_reminder',
    ['email' => 'paciente@email.com', 'name' => 'Juan Pérez', 'type' => 'patient'],
    '2025-06-14 14:30:00',
    ['date' => '15/06/2025', 'time' => '14:30'],
    $reservation_id,
    'reservation'
);
```

### Métodos Específicos
```php
// Notificar cita agendada (incluye recordatorio automático)
NotificationService::notifyAppointmentScheduled($reservation_id);

// Notificar nuevo paciente
NotificationService::notifyPatientRegistered($patient_id);

// Notificar agregado a lista de espera
NotificationService::notifyWaitlistAdded($waitlist_id);

// Notificar asignación desde lista de espera
NotificationService::notifyWaitlistAssignment($waitlist_id, $reservation_id);
```

## 📈 Monitoreo

### Dashboard
Acceda a estadísticas en tiempo real:
- **Dashboard Oncología**: Vista general de notificaciones del día
- **Notificaciones > Historial**: Estadísticas detalladas y logs

### Logs
Los logs se almacenan en:
- **Base de datos**: Tabla `notification_log`
- **Archivo**: `logs/notification_processor.log` (procesador automático)

## 🔍 Solución de Problemas

### Email no se envía
1. Verificar configuración SMTP
2. Comprobar credenciales
3. Revisar logs de error
4. Probar con "Probar Configuración"

### Recordatorios no funcionan
1. Verificar que el cron job esté configurado
2. Ejecutar manualmente: `php notification_processor.php`
3. Revisar cola de notificaciones

### Notificaciones duplicadas
1. Verificar configuración de cron job
2. Comprobar que no hay múltiples instancias ejecutándose

## 🛠️ Mantenimiento

### Limpieza Automática
El sistema limpia automáticamente:
- Logs antiguos (más de 30 días)
- Notificaciones enviadas (según configuración)

### Limpieza Manual
```sql
-- Limpiar logs antiguos
DELETE FROM notification_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Limpiar cola procesada
DELETE FROM notification_queue WHERE status = 'sent';
```

## 📞 Soporte

Para soporte técnico:
1. Revisar logs de error
2. Verificar configuración SMTP
3. Comprobar estado de las tablas de base de datos
4. Ejecutar el instalador para diagnóstico

---

**Versión**: 1.0  
**Fecha**: Junio 2025  
**Compatibilidad**: PHP 5.6+, MySQL 5.7+  
**Licencia**: Sistema Oncológico
