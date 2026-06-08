#!/bin/bash
# TechSolutions AI - Alertas Wazuh via Telegram (cron cada minuto)
# Cron root: * * * * * /usr/local/bin/wazuh_telegram.sh >> /var/log/telegram_wazuh.log 2>&1

TOKEN="TU_BOT_TOKEN"
CHAT_ID="TU_CHAT_ID"
ALERTS_LOG="/var/ossec/logs/alerts/alerts.json"
LAST_POS_FILE="/var/ossec/tmp/wazuh_tg_pos"

mkdir -p /var/ossec/tmp
touch "$LAST_POS_FILE" 2>/dev/null

send_telegram() {
    curl -s -X POST "https://api.telegram.org/bot${TOKEN}/sendMessage" \
        --data-urlencode "chat_id=${CHAT_ID}" \
        --data-urlencode "text=$1" > /dev/null 2>&1
}

nivel_texto() {
    local n=$1
    if   [ "$n" -ge 12 ]; then echo "CRITICO"
    elif [ "$n" -ge 9  ]; then echo "ALTO"
    elif [ "$n" -ge 6  ]; then echo "MEDIO"
    elif [ "$n" -ge 3  ]; then echo "BAJO"
    else echo "INFO"; fi
}

LAST=0
if [ -f "$LAST_POS_FILE" ]; then
    LAST=$(cat "$LAST_POS_FILE" | tr -d '[:space:]')
    [[ "$LAST" =~ ^[0-9]+$ ]] || LAST=0
fi

TOTAL=$(sudo cat "$ALERTS_LOG" 2>/dev/null | wc -l || echo 0)
TOTAL=$(echo "$TOTAL" | tr -d '[:space:]')
[[ "$TOTAL" =~ ^[0-9]+$ ]] || TOTAL=0

if [ "$TOTAL" -le "$LAST" ]; then
    echo "$TOTAL" > "$LAST_POS_FILE"
    exit 0
fi

sudo tail -n +$((LAST + 1)) "$ALERTS_LOG" | while IFS= read -r line; do

    [ -z "$line" ] && continue

    LEVEL=$(echo "$line" | python3 -c "
import json,sys
try:
    d=json.loads(sys.stdin.read())
    print(int(d.get('rule',{}).get('level',0)))
except:
    print(0)
" 2>/dev/null)

    LEVEL=$(echo "$LEVEL" | tr -d '[:space:]')
    [[ "$LEVEL" =~ ^[0-9]+$ ]] || continue
    [ "$LEVEL" -lt 5 ] && continue

    RULE=$(echo "$line" | python3 -c "
import json,sys
try:
    d=json.loads(sys.stdin.read())
    print(d.get('rule',{}).get('description','N/A')[:100])
except:
    print('N/A')
" 2>/dev/null)

    AGENT=$(echo "$line" | python3 -c "
import json,sys
try:
    d=json.loads(sys.stdin.read())
    print(d.get('agent',{}).get('name','N/A'))
except:
    print('N/A')
" 2>/dev/null)

    TS=$(echo "$line" | python3 -c "
import json,sys
try:
    d=json.loads(sys.stdin.read())
    print(d.get('timestamp','')[:19].replace('T',' '))
except:
    print('N/A')
" 2>/dev/null)

    NIVEL=$(nivel_texto "$LEVEL")

    MSG="[${NIVEL}] ALERTA TechSolutions AI
Descripcion: ${RULE}
Servidor: ${AGENT}
Hora: ${TS}
Nivel: ${LEVEL}/15
Panel: https://IP_SERVIDOR_SEGURIDAD"

    send_telegram "$MSG"
    sleep 2

done

echo "$TOTAL" > "$LAST_POS_FILE"
