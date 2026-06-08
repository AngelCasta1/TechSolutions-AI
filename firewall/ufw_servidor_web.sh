#!/bin/bash
# TechSolutions AI - Reglas UFW Servidor Web

sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp comment 'SSH'
sudo ufw allow 80/tcp comment 'HTTP Nginx'
sudo ufw allow 443/tcp comment 'HTTPS Nginx'
sudo ufw allow from IP_SERVIDOR_SEGURIDAD to any port 1514 proto udp comment 'Wazuh UDP'
sudo ufw allow from IP_SERVIDOR_SEGURIDAD to any port 1515 proto tcp comment 'Wazuh TCP'
sudo ufw allow from 127.0.0.1 to any port 5432 comment 'PostgreSQL local'
sudo ufw allow from IP_RED/24 to any port 445 proto tcp comment 'SMB'
sudo ufw allow from IP_RED/24 to any port 139 proto tcp comment 'NetBIOS'
sudo ufw enable
sudo ufw status verbose
