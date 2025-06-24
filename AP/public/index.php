<?php session_start();
if(isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Alienity Portal — Login</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="login-wrapper">
    <div class="login-card neon-card">
      <img src="assets/img/PortalLogo.png" alt="Alienity Portal" class="logo">
      <h1 class="neon-text">Alienity Portal</h1>
      <form action="authenticate.php" method="post" class="login-form">
        <div class="input-group">
          <input type="text"  name="username" required>
          <label>Username</label>
        </div>
        <div class="input-group">
          <input type="password" name="password" required>
          <label>Password</label>
        </div>
        <button type="submit" class="btn-neon">Accedi</button>
      </form>
      <?php if(isset($_GET['error'])): ?>
        <p class="error-text">⚠️ Credenziali errate.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
