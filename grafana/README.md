# Grafana — Paneles de monitorización

Queries SQL utilizadas en los paneles del dashboard de Grafana.
Fuente de datos: PostgreSQL — Base de datos `empresa`

---

## Panel 1 — Actividad Web

**Tipo:** Time Series  
**Objetivo:** Visualizar el tráfico del servidor web diferenciando respuestas exitosas y errores HTTP.

```sql
SELECT
  $__timeGroupAlias(fecha, $__interval),
  count(*) FILTER (WHERE codigo BETWEEN 200 AND 299) AS "OK (2xx)",
  count(*) FILTER (WHERE codigo BETWEEN 400 AND 499) AS "Errores cliente (4xx)",
  count(*) FILTER (WHERE codigo BETWEEN 500 AND 599) AS "Errores servidor (5xx)"
FROM apache_logs
WHERE $__timeFilter(fecha)
GROUP BY 1
ORDER BY 1;
```

**Configuracion recomendada:**
- Overrides: verde para 2xx, naranja para 4xx, rojo para 5xx
- Usar `$__interval` en lugar de intervalo fijo para que se adapte al zoom

---

## Panel 2 — Salud de Infraestructura

**Tipo:** Pie Chart  
**Objetivo:** Mostrar la distribucion de alertas por nivel de gravedad en las ultimas 24 horas.

```sql
SELECT
  nivel AS metric,
  count(*) AS value
FROM alertas
WHERE timestamp >= NOW() - INTERVAL '24 hours'
GROUP BY nivel;
```

**Configuracion recomendada:**
- Colores: azul para bajo, verde para medio, amarillo para alto
- Filtrar por ultimas 24h para reflejar el estado actual real

---

## Panel 3 — Alertas Criticas

**Tipo:** Stat  
**Objetivo:** Contador directo de alertas de nivel alto pendientes de atender.

```sql
SELECT
  count(*) AS "Alertas Criticas Pendientes"
FROM alertas
WHERE nivel = 'alto'
  AND enviado_tg = FALSE;
```

**Configuracion recomendada:**
- Thresholds: verde en 0, rojo en 1 o superior
- El campo `enviado_tg = FALSE` filtra solo las alertas sin atender

---

## Panel 4 — Top IPs Sospechosas

**Tipo:** Table  
**Objetivo:** Mostrar en tiempo real las IPs con mas actividad sospechosa, util para detectar ataques Nmap o Nikto.

```sql
SELECT
  ip AS "IP Origen",
  count(*) AS "Peticiones",
  count(*) FILTER (WHERE codigo >= 400) AS "Errores"
FROM apache_logs
WHERE $__timeFilter(fecha)
GROUP BY ip
ORDER BY count(*) DESC
LIMIT 10;
```

**Configuracion recomendada:**
- Threshold en columna Peticiones: rojo si mayor de 100
- Durante un ataque con Nikto la IP atacante aparece en la primera posicion

---

## Resumen de paneles

| Panel | Tipo | Tabla | Objetivo |
|---|---|---|---|
| Actividad Web | Time Series | apache_logs | Ver trafico y errores HTTP |
| Salud Infraestructura | Pie Chart | alertas | Distribucion de alertas por gravedad |
| Alertas Criticas | Stat | alertas | Contador urgente de alertas sin atender |
| Top IPs | Table | apache_logs | Detectar IPs atacantes en tiempo real |
