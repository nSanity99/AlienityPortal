<?php session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Dashboard — Alienity Portal</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <!-- Dashboard Container -->
  <div class="dashboard-wrapper neon-card">
    <!-- Header -->
    <header class="neon-text dashboard-header">
      <img src="assets/img/PortalLogo.png" alt="Logo" class="logo-large">
      <h2>Benvenuto nella tua Dashboard</h2>
      <div class="header-controls">
        <a href="manage_apps.php" class="btn-neon small">Gestisci App</a>
        <a href="logout.php" class="btn-neon small">Logout</a>
      </div>
    </header>

    <!-- App Grid -->
    <main class="apps-grid">
      <!-- App Card Disabled -->
      <div class="app-card disabled">
        <div class="icon">🗂️</div>
        <div class="label">Documenti</div>
      </div>

      <!-- App Card Disabled -->
      <div class="app-card disabled">
        <div class="icon">📅</div>
        <div class="label">Calendario</div>
      </div>

      <!-- App Card Attiva -->
      <a href="pagamenti.php" class="app-card">
        <div class="icon">💳</div>
        <div class="label">Pagamenti</div>
      </a>
    </main>
  </div>
</body>
</html>