# Config — Configuración de servicios

Fragmentos de configuración de los servicios principales de la infraestructura.

---

## smb.conf — Samba (Servidor Seguridad)

Seccion añadida en `/etc/samba/smb.conf` para el recurso compartido de backups:

```ini
[Copias_BBDD]
   path = /home/adm1/Copias_Seguridad_BBDD
   browseable = yes
   writable = yes
   valid users = tu_usuario
   create mask = 0660
   directory mask = 0770
```

---

## fstab — Montaje automático Samba (Servidor Web)

Linea añadida en `/etc/fstab` para montar automaticamente el recurso compartido:
//IP_SERVIDOR_SEGURIDAD/Copias_BBDD /mnt/backups_seguridad cifs credentials=/etc/samba/credenciales_backup,vers=3.0,uid=postgres,gid=postgres,iocharset=utf8,_netdev 0 0

Activar el montaje:
```bash
sudo mount -a
mountpoint -q /mnt/backups_seguridad && echo "Montado correctamente" || echo "Error al montar"
```

---

## credenciales_backup — Plantilla credenciales Samba

Archivo en `/etc/samba/credenciales_backup`:
username=tu_usuario
password=tu_password
domain=TU_DOMINIO

Asegurar permisos:
```bash
sudo chmod 600 /etc/samba/credenciales_backup
```

---

## ossec.conf — Wazuh Manager

Fragmento añadido en `/var/ossec/etc/ossec.conf` para el active-response de Telegram:

```xml
<command>
  <name>telegram-alert</name>
  <executable>wazuh_telegram_simple.sh</executable>
  <expect>srcip</expect>
  <timeout_allowed>no</timeout_allowed>
</command>

<active-response>
  <command>telegram-alert</command>
  <location>server</location>
  <level>5</level>
</active-response>
```

Reiniciar Wazuh tras aplicar cambios:
```bash
sudo systemctl restart wazuh-manager
```

---

## Notas de instalacion

| Servicio | Servidor | Ruta del fichero |
|---|---|---|
| Samba | Servidor seguridad | `/etc/samba/smb.conf` |
| fstab | Servidor web | `/etc/fstab` |
| Credenciales | Servidor web | `/etc/samba/credenciales_backup` |
| Wazuh | Servidor seguridad | `/var/ossec/etc/ossec.conf` |
