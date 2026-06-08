<?php
session_start();
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['usuario'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if ($user && $pass) {
        $db = getDB();
        $stmt = $db->prepare('SELECT id,password,rol FROM usuarios WHERE usuario = ?');
        $stmt->execute([$user]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && password_verify($pass, $row['password'])) {
            $_SESSION['user'] = $user;
            $_SESSION['rol']  = $row['rol'];
            header('Location: panel.php');
            exit;
        }
    }
    header('Location: login.php?error=1');
    exit;
}
header('Location: login.php');
?>
