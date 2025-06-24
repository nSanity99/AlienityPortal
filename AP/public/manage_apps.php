<?php
session_start();
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

// Handle toggling
if (isset($_POST['toggle_id'], $_POST['enabled'])) {
    $id = (int)$_POST['toggle_id'];
    $enabled = ($_POST['enabled'] === '1') ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE apps SET enabled = :e WHERE id = :id");
    $stmt->execute(['e' => $enabled, 'id' => $id]);
    header('Location: manage_apps.php');
    exit;
}

// Handle creation
if (isset($_POST['new_app']) && trim($_POST['new_app'])) {
    $name = trim($_POST['new_app']);
    $slug = preg_replace('/[^a-z0-9]+/','_',strtolower($name));
    $enabled = isset($_POST['new_enabled']) ? 1 : 0;
    // Insert into DB
    $ins = $pdo->prepare("INSERT INTO apps (name, slug, enabled) VALUES (:n, :s, :e)");
    $ins->execute(['n' => $name, 's' => $slug, 'e' => $enabled]);
    // Optionally create placeholder file
    $filePath = __DIR__ . "/app/{$slug}.php";
    if (!file_exists($filePath)) {
        file_put_contents($filePath, "<?php\n// Pagina per {$name}\necho '<h1>{$name}</h1>';\n");
    }
    header('Location: manage_apps.php');
    exit;
}

// Fetch apps
$stmt = $pdo->query("SELECT * FROM apps ORDER BY id");
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Gestione App — Alienity Portal</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="dashboard-wrapper neon-card">
    <header class="neon-text dashboard-header">
      <img src="assets/img/PortalLogo.png" alt="Logo" class="logo-large">
      <h2>Gestisci Applicazioni</h2>
      <a href="dashboard.php" class="btn-neon">Torna alla Dashboard</a>
    </header>

    <main class="apps-manage-grid">
      <?php foreach ($apps as $app): ?>
        <div class="app-card <?php echo $app['enabled'] ? '' : 'disabled'; ?>">
          <div class="icon">🔧</div>
          <div class="label"><?php echo htmlspecialchars($app['name']); ?></div>
          <div class="controls">
            <?php if ($app['enabled']): ?>
              <a href="app/<?php echo $app['slug']; ?>.php" class="btn-neon small">Apri</a>
            <?php endif; ?>
            <form method="post" class="toggle-form">
              <input type="hidden" name="toggle_id" value="<?php echo $app['id']; ?>">
              <input type="hidden" name="enabled" value="<?php echo $app['enabled'] ? '0':'1'; ?>">
              <button type="submit" class="btn-neon small">
                <?php echo $app['enabled'] ? 'Disabilita':'Abilita'; ?>
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="app-card new-app-card">
        <form method="post" class="new-app-form">
          <div class="input-group">
            <input type="text" name="new_app" required placeholder=" ">
            <label>Nuova App</label>
          </div>
          <label class="checkbox-inline">
            <input type="checkbox" name="new_enabled" value="1"> Abilita
          </label>
          <button type="submit" class="btn-neon small">Crea</button>
        </form>
      </div>
    </main>
  </div>
</body>
</html>