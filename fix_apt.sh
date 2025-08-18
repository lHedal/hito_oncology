#!/bin/bash

# Script para solucionar problemas de APT/DPKG locks
# Sistema Oncológico - Linux Mint

echo "🔧 Solucionador de problemas APT/DPKG"
echo "===================================="

# Función para mostrar procesos APT activos
show_apt_processes() {
    echo "📋 Procesos APT/DPKG activos:"
    ps aux | grep -E 'apt|dpkg|unattended-upgrade' | grep -v grep
}

# Función para limpiar locks
clean_locks() {
    echo "🧹 Limpiando archivos de lock..."
    sudo rm -f /var/lib/dpkg/lock-frontend
    sudo rm -f /var/lib/dpkg/lock
    sudo rm -f /var/cache/apt/archives/lock
    echo "✅ Locks eliminados"
}

# Función para terminar procesos
kill_apt_processes() {
    echo "⚠️  Terminando procesos APT/DPKG..."
    sudo killall apt apt-get dpkg unattended-upgrades 2>/dev/null || true
    echo "✅ Procesos terminados"
}

# Función para reconfigurar DPKG
reconfigure_dpkg() {
    echo "🔄 Reconfigurando DPKG..."
    sudo dpkg --configure -a
    echo "✅ DPKG reconfigurado"
}

# Función para verificar el estado
verify_system() {
    echo "🧪 Verificando sistema APT..."
    if sudo apt update > /dev/null 2>&1; then
        echo "✅ Sistema APT funcionando correctamente"
        return 0
    else
        echo "❌ Sistema APT aún tiene problemas"
        return 1
    fi
}

# Menú principal
echo "Selecciona una opción:"
echo "1) Solución automática (recomendada)"
echo "2) Ver procesos APT activos"
echo "3) Limpiar locks manualmente"
echo "4) Terminar procesos APT"
echo "5) Reconfigurar DPKG"
echo "6) Verificar estado del sistema"
echo "7) Solución completa (todo)"
echo "0) Salir"

read -p "Opción: " option

case $option in
    1)
        echo "🚀 Ejecutando solución automática..."
        show_apt_processes
        kill_apt_processes
        clean_locks
        reconfigure_dpkg
        if verify_system; then
            echo "🎉 ¡Problema resuelto!"
        else
            echo "⚠️  Puedes intentar reiniciar el sistema"
        fi
        ;;
    2)
        show_apt_processes
        ;;
    3)
        clean_locks
        ;;
    4)
        kill_apt_processes
        ;;
    5)
        reconfigure_dpkg
        ;;
    6)
        verify_system
        ;;
    7)
        echo "🚀 Ejecutando solución completa..."
        echo "🛑 Deteniendo servicios de actualización automática..."
        sudo systemctl stop unattended-upgrades 2>/dev/null || true
        sudo systemctl stop apt-daily.timer 2>/dev/null || true
        sudo systemctl stop apt-daily-upgrade.timer 2>/dev/null || true
        
        show_apt_processes
        kill_apt_processes
        clean_locks
        reconfigure_dpkg
        
        echo "🔄 Actualizando índices..."
        sudo apt update
        
        echo "🔄 Reiniciando servicios..."
        sudo systemctl start unattended-upgrades 2>/dev/null || true
        sudo systemctl start apt-daily.timer 2>/dev/null || true
        sudo systemctl start apt-daily-upgrade.timer 2>/dev/null || true
        
        if verify_system; then
            echo "🎉 ¡Sistema completamente reparado!"
        else
            echo "⚠️  Considera reiniciar el sistema"
        fi
        ;;
    0)
        echo "👋 Saliendo..."
        exit 0
        ;;
    *)
        echo "❌ Opción inválida"
        exit 1
        ;;
esac

echo ""
echo "💡 Comandos útiles:"
echo "   - Verificar APT: sudo apt update"
echo "   - Ver procesos: ps aux | grep apt"
echo "   - Reiniciar si es necesario: sudo reboot"
