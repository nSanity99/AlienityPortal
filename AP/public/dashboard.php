<?php session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$configPath = dirname(__DIR__) . '/config/DBConfig.php';
if (!file_exists($configPath)) {
    $configPath = __DIR__ . '/../config/DBConfig.php';
}
require_once $configPath;
$pdo = DBConfig::getConnection();
$apps = $pdo->query("SELECT * FROM apps ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
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
      <?php foreach ($apps as $app): ?>
        <?php $class = $app['enabled'] ? 'app-card' : 'app-card disabled'; ?>
        <?php $href = $app['enabled'] ? "app/{$app['slug']}.php" : '#'; ?>
        <a href="<?php echo $href; ?>" class="<?php echo $class; ?>">
          <div class="icon">📦</div>
          <div class="label"><?php echo htmlspecialchars($app['name']); ?></div>
        </a>
      <?php endforeach; ?>
    </main>
  </div>
</body>
</html>