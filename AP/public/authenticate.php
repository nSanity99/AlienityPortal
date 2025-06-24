<?php
session_start();
require_once __DIR__ . '/../config/DBConfig.php';

$user = $_POST['username'] ?? '';
$pass = $_POST['password'] ?? '';

try {
    $pdo = DBConfig::getConnection();
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :u");
    $stmt->execute(['u'=>$user]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if($row && password_verify($pass, $row['password_hash'])) {
        $_SESSION['user'] = $row['id'];
        header('Location: dashboard.php');
        exit;
    } else {
        header('Location: index.php?error=1');
        exit;
    }
} catch(PDOException $e) {
    die("Errore DB: " . $e->getMessage());
}
