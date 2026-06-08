<?php
session_start();
if (isset($_SESSION['user'])) { header('Location: panel.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login – TechSolutions AI</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;background:#0a1628;min-height:100vh;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden}
.bg{position:absolute;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(37,99,235,0.2) 0%,transparent 60%),radial-gradient(ellipse 50% 40% at 80% 80%,rgba(168,85,247,0.15) 0%,transparent 50%)}
.grid{position:absolute;inset:0;background-image:linear-gradient(rgba(0,212,255,0.03) 1px,transparent 1px),linear-gradient(90deg,rgba(0,212,255,0.03) 1px,transparent 1px);background-size:50px 50px}
.card{background:rgba(15,23,42,0.9);border:1px solid rgba(255,255,255,0.08);border-radius:20px;padding:2.5rem;width:100%;max-width:420px;position:relative;z-index:2;backdrop-filter:blur(20px)}
.logo{text-align:center;margin-bottom:2rem}
.logo-icon{width:56px;height:56px;background:linear-gradient(135deg,#2563eb,#a855f7);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin:0 auto .8rem}
.logo h1{font-size:1.4rem;font-weight:800;color:#fff}
.logo h1 span{background:linear-gradient(135deg,#00d4ff,#a855f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.logo p{font-size:.82rem;color:#475569;margin-top:.3rem}
.badge-restricted{display:inline-flex;align-items:center;gap:6px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#f87171;border-radius:20px;padding:4px 14px;font-size:.75rem;margin-bottom:1.5rem}
.form-group{margin-bottom:1.2rem}
.form-group label{display:block;font-size:.82rem;color:#94a3b8;margin-bottom:.5rem;font-weight:500}
.form-group input{width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:12px 16px;font-size:.9rem;color:#e2e8f0;outline:none;transition:border-color .2s}
.form-group input:focus{border-color:rgba(37,99,235,0.5);background:rgba(37,99,235,0.05)}
.btn-login{width:100%;background:linear-gradient(135deg,#2563eb,#a855f7);color:#fff;border:none;border-radius:10px;padding:13px;font-size:1rem;font-weight:700;cursor:pointer;transition:opacity .2s;margin-top:.5rem}
.btn-login:hover{opacity:.85}
.error{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#f87171;border-radius:8px;padding:10px 14px;font-size:.82rem;margin-bottom:1rem;text-align:center}
.back{text-align:center;margin-top:1.2rem}
.back a{color:#475569;font-size:.82rem;text-decoration:none;transition:color .2s}
.back a:hover{color:#00d4ff}
</style>
</head>
<body>
<div class="bg"></div>
<div class="grid"></div>
<div class="card">
  <div class="logo">
    <div class="logo-icon">🧠</div>
    <h1>TechSolutions <span>AI</span></h1>
    <p>Panel de Seguridad</p>
  </div>
  <div style="text-align:center">
    <span class="badge-restricted">🔒 Acceso restringido</span>
  </div>
  <?php if(isset($_GET['error'])): ?>
  <div class="error">Credenciales incorrectas. Inténtalo de nuevo.</div>
  <?php endif; ?>
  <form method="POST" action="auth.php">
    <div class="form-group">
      <label>Usuario</label>
      <input type="text" name="usuario" placeholder="Administrador" required autofocus>
    </div>
    <div class="form-group">
      <label>Contraseña</label>
      <input type="password" name="password" placeholder="••••••••••" required>
    </div>
    <button type="submit" class="btn-login">Iniciar sesión →</button>
  </form>
  <div class="back"><a href="index.html">← Volver al inicio</a></div>
</div>
</body>
</html>
