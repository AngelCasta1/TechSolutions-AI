#!/bin/bash
# TechSolutions AI - Script de copia de seguridad de PostgreSQL
# Cron: 0 3 * * * /usr/local/bin/backup_postgres.sh >> /var/log/backup_postgres.log 2>&1

DB_NAME='empresa'
DB_USER='tu_usuario_postgres'
export PGPASSWORD='tu_password_postgres'

BACKUP_LOCAL='/var/backups/postgres'
BACKUP_REMOTO='/mnt/backups_seguridad'
FECHA=$(date +%Y-%m-%d_%H-%M)
ARCHIVO="backup_${DB_NAME}_${FECHA}.sql.gz"

mkdir -p "$BACKUP_LOCAL"

pg_dump -h 127.0.0.1 -U "$DB_USER" "$DB_NAME" | gzip > "$BACKUP_LOCAL/$ARCHIVO"

if [ $? -eq 0 ]; then
    echo "[$(date)] Backup creado: $ARCHIVO"

    if mountpoint -q "$BACKUP_REMOTO"; then
        cp "$BACKUP_LOCAL/$ARCHIVO" "$BACKUP_REMOTO/"
        echo "[$(date)] Backup enviado al servidor de seguridad"
    else
        echo "[$(date)] Carpeta remota no montada, backup solo en local"
    fi

    find "$BACKUP_LOCAL" -name '*.sql.gz' -mtime +7 -delete
    find "$BACKUP_REMOTO" -name '*.sql.gz' -mtime +30 -delete 2>/dev/null

else
    echo "[$(date)] ERROR al crear el backup"
    exit 1
fi
