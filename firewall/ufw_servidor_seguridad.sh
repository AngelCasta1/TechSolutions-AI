#!/bin/bash
# TechSolutions AI - Reglas UFW Servidor Seguridad

sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp comment 'SSH'
sudo ufw allow 443/tcp comment 'Wazuh Dashboard HTTPS'
sudo ufw allow 1514/udp comment 'Wazuh agentes UDP'
sudo ufw allow 1514/tcp comment 'Wazuh agentes TCP'
sudo ufw allow 1515/tcp comment 'Wazuh registro agentes'
sudo ufw allow 1516/tcp comment 'Wazuh cluster'
sudo ufw allow 9200/tcp comment 'Wazuh Indexer'
sudo ufw allow 5601/tcp comment 'Kibana'
sudo ufw allow 3000/tcp comment 'Grafana'
sudo ufw allow from IP_SERVIDOR_WEB to any port 445 proto tcp comment 'SMB backups'
sudo ufw allow from IP_SERVIDOR_WEB to any port 139 proto tcp comment 'NetBIOS'
sudo ufw enable
sudo ufw status verbose
