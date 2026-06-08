# 🧠 TechSolutions AI — TFG ASIR

> **Diseño e Implementación de una Infraestructura Corporativa Segura con Monitorización y Automatización**

Trabajo de Fin de Grado — Administración de Sistemas Informáticos en Red (ASIR)  
**Autores:** Iván Guerrero Antona · Ángel Castaño Arias

---

## Descripción

Este repositorio contiene los scripts, configuraciones y documentación del TFG de la empresa simulada **TechSolutions AI**. El proyecto diseña e implementa una infraestructura corporativa virtualizada orientada a la seguridad informática, la monitorización de eventos en tiempo real y la automatización de tareas esenciales.

---

## Infraestructura

| Servidor | IP | SO | Rol |
|---|---|---|---|
| Controlador de dominio | 192.168.10.10 | Windows Server 2016 | Active Directory + DNS |
| Servidor web | 192.168.10.20 | Ubuntu Server 22.04 | Nginx + PostgreSQL + Agente Wazuh |
| Servidor seguridad | 192.168.10.30 | Ubuntu Server 22.04 | Wazuh + Grafana |
| Cliente | 192.168.10.50 | Windows 10 Pro | Accesos y permisos del dominio |

Red: `192.168.10.0/24` — Virtualización: Oracle VM VirtualBox

---

## Tecnologías utilizadas

![VirtualBox](https://img.shields.io/badge/VirtualBox-183A61?style=flat&logo=virtualbox&logoColor=white)
![Ubuntu](https://img.shields.io/badge/Ubuntu_22.04-E95420?style=flat&logo=ubuntu&logoColor=white)
![Windows Server](https://img.shields.io/badge/Windows_Server_2016-0078D6?style=flat&logo=windows&logoColor=white)
![Nginx](https://img.shields.io/badge/Nginx-009639?style=flat&logo=nginx&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-4169E1?style=flat&logo=postgresql&logoColor=white)
![Wazuh](https://img.shields.io/badge/Wazuh-SIEM-1D5C8C?style=flat&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAzMiAzMiI+PHBhdGggZmlsbD0id2hpdGUiIGQ9Ik0xNiAyQzguMyAyIDIgOC4zIDIgMTZzNi4zIDE0IDE0IDE0IDE0LTYuMyAxNC0xNFMyMy43IDIgMTYgMnoiLz48L3N2Zz4=&logoColor=white)
![Grafana](https://img.shields.io/badge/Grafana-F46800?style=flat&logo=grafana&logoColor=white)
![Bash](https://img.shields.io/badge/Bash-4EAA25?style=flat&logo=gnubash&logoColor=white)
![Samba](https://img.shields.io/badge/Samba-CC0000?style=flat&logoColor=white)
![Telegram](https://img.shields.io/badge/Telegram_Bot-26A5E4?style=flat&logo=telegram&logoColor=white)
![Active Directory](https://img.shields.io/badge/Active_Directory-0078D6?style=flat&logo=microsoft&logoColor=white)
![UFW](https://img.shields.io/badge/UFW-Firewall-E95420?style=flat&logo=ubuntu&logoColor=white)
![Hydra](https://img.shields.io/badge/Hydra-Pentest-darkred?style=flat&logo=kalilinux&logoColor=white)
![Nmap](https://img.shields.io/badge/Nmap-Network_Scanner-0E83CD?style=flat&logoColor=white)
![Nikto](https://img.shields.io/badge/Nikto-Web_Scanner-green?style=flat&logoColor=white)
![Cron](https://img.shields.io/badge/Cron-Automatizacion-555555?style=flat&logo=clockify&logoColor=white)
![OpenSearch](https://img.shields.io/badge/OpenSearch-005EB8?style=flat&logo=opensearch&logoColor=white)

---

## Estructura del repositorio

```
TechSolutions-AI-TFG/
│
├── scripts/
│   ├── backup_postgres.sh          # Copia de seguridad automatizada de PostgreSQL
│   └── wazuh_telegram.sh           # Alertas en tiempo real via Telegram (cron)
│
├── config/
│   └── README.md          # Configuración
│
├── web/
│   ├── index.html          # Intranet Corporativa
│   ├── login.php           # Login panel administración
│   ├── auth.php            # Autenticación
│   └── panel.php           # Panel administración Intranet Corporativa
│
├── grafana/
│   └── README.md           # Query paneles
│
├── firewall/
│   ├── ufw_servidor_web.sh         # Reglas UFW servidor web (192.168.10.20)
│   └── ufw_servidor_seguridad.sh   # Reglas UFW servidor seguridad (192.168.10.30)
│
└── README.md
```

---

## Scripts principales

### Backup automatizado de PostgreSQL

Realiza un volcado comprimido de la base de datos y lo envía al servidor de seguridad via Samba. Retención de 7 días en local y 30 días en remoto.

```bash
# Ejecutar manualmente
sudo /usr/local/bin/backup_postgres.sh

# Cron — todos los días a las 3:00 AM
0 3 * * * /usr/local/bin/backup_postgres.sh >> /var/log/backup_postgres.log 2>&1
```

### Alertas Telegram via Wazuh

Notificaciones automáticas al móvil del administrador cuando Wazuh detecta eventos de nivel 5 o superior.

```bash
# Cron en root — cada minuto
* * * * * /usr/local/bin/wazuh_telegram.sh >> /var/log/telegram_wazuh.log 2>&1
```

---

## Dashboards Grafana

| Panel | Tipo | Descripción |
|---|---|---|
| Actividad Web | Time Series | Tráfico HTTP por código de respuesta (2xx, 4xx, 5xx) |
| Salud Infraestructura | Pie Chart | Distribución de alertas por nivel (bajo, medio, alto) |
| Alertas Críticas | Stat | Contador de alertas nivel alto pendientes |
| Top IPs | Table | IPs con más peticiones sospechosas en tiempo real |

---

## Auditoría de seguridad

Pruebas controladas realizadas para validar el sistema de monitorización:

```bash
# Reconocimiento de red
nmap -A -T4 192.168.10.0/24

# Fuerza bruta SSH
hydra -l usuario -P /tmp/passwords.txt ssh://192.168.10.20 -t 4 -V

# Escaneo web
nikto -h http://192.168.10.20

# Escalada de privilegios
sudo cat /etc/shadow
```

Todas las pruebas generan alertas en Wazuh y notificaciones automáticas en Telegram.

---

## Copias de seguridad

| Ubicación | Ruta | Retención |
|---|---|---|
| Local (servidor web) | `/var/backups/postgres/` | 7 días |
| Remoto (servidor seguridad) | `/home/adm1/Copias_Seguridad_BBDD/` | 30 días |

Montaje via Samba con autenticación y cifrado (`vers=3.0`).

---

## Autores

**Iván Guerrero Antona** · **Ángel Castaño Arias**  
ASIR — Administración de Sistemas Informáticos en Red

---

*TechSolutions AI — Proyecto académico con fines educativos*
