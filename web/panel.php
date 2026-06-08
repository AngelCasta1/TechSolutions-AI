<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }

$db = getDB();

// Stats
$total    = $db->query('SELECT COUNT(*) FROM apache_logs')->fetchColumn();
$err404   = $db->query("SELECT COUNT(*) FROM apache_logs WHERE codigo=404")->fetchColumn();
$err500   = $db->query("SELECT COUNT(*) FROM apache_logs WHERE codigo=500")->fetchColumn();
$alertas  = $db->query("SELECT COUNT(*) FROM alertas WHERE DATE(timestamp)=CURRENT_DATE")->fetchColumn();

// Logs recientes
$logs = $db->query("SELECT ip,fecha,metodo,recurso,codigo,user_agent FROM apache_logs ORDER BY fecha DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);

// IPs sospechosas
$sospechosas = $db->query("
  SELECT ip,
    COUNT(*) as peticiones,
    SUM(CASE WHEN codigo>=400 THEN 1 ELSE 0 END) as errores,
    ROUND(SUM(CASE WHEN codigo>=400 THEN 1 ELSE 0 END)*100.0/COUNT(*),1) as pct_error,
    MAX(fecha) as ultima
  FROM apache_logs
  GROUP BY ip
  HAVING COUNT(*)>10 OR SUM(CASE WHEN codigo>=400 THEN 1 ELSE 0 END)>5
  ORDER BY errores DESC
  LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

// Visitas por minuto (últimos 10 min)
$visitas_min = $db->query("
  SELECT to_char(date_trunc('minute',fecha),'HH24:MI') as minuto, COUNT(*) as total
  FROM apache_logs
  WHERE fecha >= NOW() - INTERVAL '10 minutes'
  GROUP BY date_trunc('minute',fecha)
  ORDER BY date_trunc('minute',fecha)
")->fetchAll(PDO::FETCH_ASSOC);

// Alertas recientes
$alertas_rec = $db->query("SELECT tipo,descripcion,ip_origen,servidor,nivel,timestamp FROM alertas ORDER BY timestamp DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel de Seguridad – TechSolutions AI</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
:root{--p:#0a1628;--a:#2563eb;--neon:#00d4ff;--s:#0f172a}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;background:var(--s);color:#e2e8f0;min-height:100vh}
.topbar{background:rgba(10,22,40,0.95);border-bottom:1px solid rgba(0,212,255,0.15);padding:0 1.5rem;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;backdrop-filter:blur(20px)}
.topbar-brand{font-size:1.1rem;font-weight:800;color:#fff;display:flex;align-items:center;gap:8px}
.brand-dot{width:10px;height:10px;border-radius:50%;background:#00d4ff;box-shadow:0 0 8px #00d4ff;animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.topbar-right{display:flex;align-items:center;gap:1rem}
.user-badge{background:rgba(37,99,235,0.2);border:1px solid rgba(37,99,235,0.3);color:#60a5fa;border-radius:8px;padding:4px 12px;font-size:.8rem;font-weight:600}
.btn-logout{background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.25);color:#f87171;border-radius:8px;padding:5px 14px;font-size:.8rem;text-decoration:none;transition:all .2s}
.btn-logout:hover{background:rgba(239,68,68,0.25);color:#f87171}
.sidebar{width:220px;background:rgba(10,22,40,0.8);border-right:1px solid rgba(255,255,255,0.06);position:fixed;top:60px;left:0;bottom:0;padding:1.5rem 1rem;display:flex;flex-direction:column;gap:.3rem;overflow-y:auto}
.nav-item-side{display:flex;align-items:center;gap:.7rem;padding:.7rem 1rem;border-radius:10px;color:#64748b;font-size:.87rem;font-weight:500;cursor:pointer;transition:all .2s;text-decoration:none;border:none;background:none;width:100%;text-align:left}
.nav-item-side:hover,.nav-item-side.active{background:rgba(37,99,235,0.15);color:#60a5fa;border:1px solid rgba(37,99,235,0.2) !important}
.nav-item-side i{width:16px;text-align:center}
.nav-sep{height:1px;background:rgba(255,255,255,0.06);margin:.5rem 0}
.main{margin-left:220px;padding:1.5rem;min-height:calc(100vh - 60px)}
.page{display:none}
.page.active{display:block}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem}
.stat-card{background:rgba(15,23,42,0.8);border:1px solid rgba(255,255,255,0.07);border-radius:14px;padding:1.2rem;transition:all .3s}
.stat-card:hover{border-color:rgba(0,212,255,0.2);transform:translateY(-2px)}
.stat-label{font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem;display:flex;align-items:center;gap:.4rem}
.stat-num{font-size:2rem;font-weight:900}
.stat-num.blue{background:linear-gradient(135deg,#00d4ff,#2563eb);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.stat-num.red{background:linear-gradient(135deg,#ef4444,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.stat-num.green{background:linear-gradient(135deg,#22c55e,#10b981);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.stat-num.purple{background:linear-gradient(135deg,#a855f7,#ec4899);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.panel-card{background:rgba(15,23,42,0.8);border:1px solid rgba(255,255,255,0.07);border-radius:16px;padding:1.3rem;margin-bottom:1.2rem}
.panel-card-title{font-size:.9rem;font-weight:700;color:#94a3b8;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;text-transform:uppercase;letter-spacing:.05em}
.table-dark-custom{width:100%;border-collapse:collapse;font-size:.82rem}
.table-dark-custom th{background:rgba(37,99,235,0.15);color:#60a5fa;padding:.7rem 1rem;text-align:left;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid rgba(255,255,255,0.06)}
.table-dark-custom td{padding:.65rem 1rem;border-bottom:1px solid rgba(255,255,255,0.04);color:#94a3b8;vertical-align:middle}
.table-dark-custom tr:hover td{background:rgba(37,99,235,0.05);color:#e2e8f0}
.code-pill{font-family:'Courier New',monospace;font-size:.78rem;border-radius:6px;padding:2px 8px;font-weight:700}
.code-pill.ok{background:rgba(34,197,94,0.15);color:#4ade80}
.code-pill.warn{background:rgba(245,158,11,0.15);color:#fbbf24}
.code-pill.err{background:rgba(239,68,68,0.15);color:#f87171}
.method-pill{font-family:'Courier New',monospace;font-size:.75rem;border-radius:5px;padding:2px 8px;font-weight:700;background:rgba(37,99,235,0.15);color:#60a5fa}
.ip-text{font-family:'Courier New',monospace;font-size:.8rem;color:#00d4ff}
.risk-badge{border-radius:6px;padding:3px 10px;font-size:.72rem;font-weight:700}
.risk-badge.bajo{background:rgba(34,197,94,0.15);color:#4ade80}
.risk-badge.medio{background:rgba(245,158,11,0.15);color:#fbbf24}
.risk-badge.alto{background:rgba(239,68,68,0.15);color:#f87171}
.risk-badge.critico{background:rgba(168,85,247,0.2);color:#c084fc}
.alert-row{display:flex;align-items:flex-start;gap:.8rem;padding:.8rem;background:rgba(15,23,42,0.6);border:1px solid rgba(255,255,255,0.05);border-radius:10px;margin-bottom:.5rem}
.alert-lvl{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800;flex-shrink:0}
.alert-lvl.crit{background:rgba(239,68,68,0.2);color:#f87171}
.alert-lvl.warn{background:rgba(245,158,11,0.2);color:#fbbf24}
.alert-lvl.info{background:rgba(34,197,94,0.2);color:#4ade80}
.search-bar{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:8px 14px;color:#e2e8f0;font-size:.85rem;outline:none;width:100%;margin-bottom:1rem;transition:border-color .2s}
.search-bar:focus{border-color:rgba(37,99,235,0.4)}
.filter-row{display:flex;gap:.7rem;flex-wrap:wrap;margin-bottom:1rem}
.filter-select{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:7px 12px;color:#e2e8f0;font-size:.82rem;outline:none;cursor:pointer}
.filter-select option{background:#0f172a}
.chart-wrap{position:relative;height:200px}
.overflow-x{overflow-x:auto}
@media(max-width:768px){
  .sidebar{display:none}
  .main{margin-left:0}
  .stats-grid{grid-template-columns:1fr 1fr}
}
</style>
</head>
<body>

<div class="topbar">
  <div class="topbar-brand">
    <div class="brand-dot"></div>
    TechSolutions AI · Panel de Seguridad
  </div>
  <div class="topbar-right">
    <span class="user-badge"><i class="fas fa-user-shield me-1"></i><?= htmlspecialchars($_SESSION['user']) ?></span>
    <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt me-1"></i>Salir</a>
  </div>
</div>

<div class="sidebar">
  <button class="nav-item-side active" onclick="showPage('dashboard')"><i class="fas fa-tachometer-alt"></i> Dashboard</button>
  <button class="nav-item-side" onclick="showPage('logs')"><i class="fas fa-list-alt"></i> Logs Web</button>
  <button class="nav-item-side" onclick="showPage('ips')"><i class="fas fa-exclamation-triangle"></i> IPs Sospechosas</button>
  <button class="nav-item-side" onclick="showPage('alertas')"><i class="fas fa-bell"></i> Alertas</button>
  <div class="nav-sep"></div>
  <a class="nav-item-side" href="index.html"><i class="fas fa-home"></i> Inicio</a>
</div>

<div class="main">

  <!-- DASHBOARD -->
  <div class="page active" id="page-dashboard">
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label"><i class="fas fa-globe"></i> Total visitas</div>
        <div class="stat-num blue"><?= number_format($total) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label"><i class="fas fa-times-circle"></i> Errores 404</div>
        <div class="stat-num red"><?= number_format($err404) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label"><i class="fas fa-bomb"></i> Errores 500</div>
        <div class="stat-num red"><?= number_format($err500) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label"><i class="fas fa-bell"></i> Alertas hoy</div>
        <div class="stat-num purple"><?= number_format($alertas) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label"><i class="fas fa-shield-alt"></i> IPs sospechosas</div>
        <div class="stat-num red"><?= count($sospechosas) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label"><i class="fas fa-check-circle"></i> Estado sistema</div>
        <div class="stat-num green">OK</div>
      </div>
    </div>

    <div class="panel-card">
      <div class="panel-card-title"><i class="fas fa-chart-line"></i> Visitas por minuto (últimos 10 min)</div>
      <div class="chart-wrap">
        <canvas id="chartVisitas"></canvas>
      </div>
    </div>

    <div class="panel-card">
      <div class="panel-card-title"><i class="fas fa-bell"></i> Últimas alertas</div>
      <?php if(empty($alertas_rec)): ?>
        <p style="color:#475569;font-size:.85rem;text-align:center;padding:1rem">No hay alertas registradas</p>
      <?php else: ?>
        <?php foreach($alertas_rec as $a): ?>
        <div class="alert-row">
          <div class="alert-lvl <?= $a['nivel']==='critico'?'crit':($a['nivel']==='alto'?'warn':'info') ?>">
            <?= strtoupper(substr($a['nivel'],0,3)) ?>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-size:.85rem;font-weight:700;color:#e2e8f0"><?= htmlspecialchars($a['tipo']) ?></div>
            <div style="font-size:.78rem;color:#475569;font-family:'Courier New',monospace">
              <?= htmlspecialchars($a['ip_origen']) ?> · <?= htmlspecialchars($a['servidor']) ?> · <?= $a['timestamp'] ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- LOGS -->
  <div class="page" id="page-logs">
    <div class="panel-card">
      <div class="panel-card-title"><i class="fas fa-list-alt"></i> Registros del servidor web</div>
      <input type="text" class="search-bar" id="searchLogs" placeholder="Buscar por IP, recurso, método..." onkeyup="filterLogs()">
      <div class="filter-row">
        <select class="filter-select" id="filterMetodo" onchange="filterLogs()">
          <option value="">Todos los métodos</option>
          <option value="GET">GET</option>
          <option value="POST">POST</option>
          <option value="PUT">PUT</option>
          <option value="DELETE">DELETE</option>
        </select>
        <select class="filter-select" id="filterCodigo" onchange="filterLogs()">
          <option value="">Todos los códigos</option>
          <option value="200">200 OK</option>
          <option value="301">301 Redirect</option>
          <option value="404">404 Not Found</option>
          <option value="500">500 Error</option>
        </select>
      </div>
      <div class="overflow-x">
        <table class="table-dark-custom" id="logsTable">
          <thead><tr><th>IP</th><th>Fecha</th><th>Método</th><th>Recurso</th><th>Código</th></tr></thead>
          <tbody>
            <?php foreach($logs as $l): ?>
            <tr>
              <td><span class="ip-text"><?= htmlspecialchars($l['ip']) ?></span></td>
              <td style="font-size:.78rem;color:#475569"><?= $l['fecha'] ?></td>
              <td><span class="method-pill"><?= htmlspecialchars($l['metodo']) ?></span></td>
              <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($l['recurso']) ?></td>
              <td>
                <?php $c=$l['codigo']; ?>
                <span class="code-pill <?= $c<400?'ok':($c<500?'warn':'err') ?>"><?= $c ?></span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- IPS SOSPECHOSAS -->
  <div class="page" id="page-ips">
    <div class="panel-card">
      <div class="panel-card-title"><i class="fas fa-exclamation-triangle"></i> IPs sospechosas detectadas</div>
      <div class="overflow-x">
        <table class="table-dark-custom">
          <thead><tr><th>IP</th><th>Peticiones</th><th>Errores</th><th>% Error</th><th>Última actividad</th><th>Riesgo</th></tr></thead>
          <tbody>
            <?php foreach($sospechosas as $s):
              $pct = floatval($s['pct_error']);
              $riesgo = $pct>50?'critico':($pct>30?'alto':($pct>15?'medio':'bajo'));
            ?>
            <tr>
              <td><span class="ip-text"><?= htmlspecialchars($s['ip']) ?></span></td>
              <td style="color:#e2e8f0;font-weight:700"><?= $s['peticiones'] ?></td>
              <td style="color:#f87171"><?= $s['errores'] ?></td>
              <td style="color:#fbbf24"><?= $s['pct_error'] ?>%</td>
              <td style="font-size:.78rem;color:#475569"><?= $s['ultima'] ?></td>
              <td><span class="risk-badge <?= $riesgo ?>"><?= strtoupper($riesgo) ?></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($sospechosas)): ?>
            <tr><td colspan="6" style="text-align:center;color:#475569;padding:2rem">No se detectaron IPs sospechosas</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ALERTAS -->
  <div class="page" id="page-alertas">
    <div class="panel-card">
      <div class="panel-card-title"><i class="fas fa-bell"></i> Historial de alertas de seguridad</div>
      <?php
      $todas = $db->query("SELECT * FROM alertas ORDER BY timestamp DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
      if(empty($todas)):
      ?>
        <p style="color:#475569;font-size:.85rem;text-align:center;padding:2rem">No hay alertas registradas. Se generarán al instalar Wazuh.</p>
      <?php else: ?>
        <?php foreach($todas as $a): ?>
        <div class="alert-row">
          <div class="alert-lvl <?= $a['nivel']==='critico'?'crit':($a['nivel']==='alto'?'warn':'info') ?>">
            <?= strtoupper(substr($a['nivel'],0,3)) ?>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-size:.85rem;font-weight:700;color:#e2e8f0"><?= htmlspecialchars($a['tipo']) ?></div>
            <div style="font-size:.82rem;color:#64748b;margin:.2rem 0"><?= htmlspecialchars($a['descripcion']) ?></div>
            <div style="font-size:.75rem;color:#334155;font-family:'Courier New',monospace">
              IP: <?= htmlspecialchars($a['ip_origen']) ?> · Servidor: <?= htmlspecialchars($a['servidor']) ?> · <?= $a['timestamp'] ?>
              <?= $a['enviado_tg']?'<span style="color:#22c55e;margin-left:.5rem">✓ Telegram</span>':'' ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>

<script>
function showPage(name) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item-side').forEach(n => n.classList.remove('active'));
  document.getElementById('page-' + name).classList.add('active');
  event.currentTarget.classList.add('active');
}

function filterLogs() {
  const q = document.getElementById('searchLogs').value.toLowerCase();
  const m = document.getElementById('filterMetodo').value;
  const c = document.getElementById('filterCodigo').value;
  document.querySelectorAll('#logsTable tbody tr').forEach(row => {
    const text = row.textContent.toLowerCase();
    const met = m ? row.cells[2].textContent.trim() === m : true;
    const cod = c ? row.cells[4].textContent.trim() === c : true;
    row.style.display = text.includes(q) && met && cod ? '' : 'none';
  });
}

// Chart
const labels = <?= json_encode(array_column($visitas_min,'minuto')) ?>;
const data   = <?= json_encode(array_column($visitas_min,'total')) ?>;
if(document.getElementById('chartVisitas')) {
  new Chart(document.getElementById('chartVisitas'), {
    type: 'line',
    data: {
      labels: labels.length ? labels : ['Sin datos'],
      datasets: [{
        label: 'Visitas',
        data: data.length ? data : [0],
        borderColor: '#00d4ff',
        backgroundColor: 'rgba(0,212,255,0.08)',
        borderWidth: 2,
        pointBackgroundColor: '#00d4ff',
        pointRadius: 4,
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { color: '#475569', font: { size: 11 } }, grid: { color: 'rgba(255,255,255,0.04)' } },
        y: { ticks: { color: '#475569', font: { size: 11 } }, grid: { color: 'rgba(255,255,255,0.04)' } }
      }
    }
  });
}

// Actualizar cada 30s
setTimeout(() => location.reload(), 30000);
</script>
</body>
</html>
