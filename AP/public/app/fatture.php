<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}
$configPath = dirname(__DIR__, 2) . '/config/DBConfig.php';
if (!file_exists($configPath)) {
    $configPath = __DIR__ . '/../config/DBConfig.php';
}
require_once $configPath;
$pdo = DBConfig::getConnection();

$errors = [];
$success = false;

$uploadDir = __DIR__ . '/../uploads/invoices';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['invoice']) && isset($_POST['title'])) {
    $title = trim($_POST['title']);
    $file  = $_FILES['invoice'];
    if ($title === '') $errors[] = 'Il titolo è obbligatorio.';
    if ($file['error'] !== UPLOAD_ERR_OK) $errors[] = 'Errore nel caricamento del file.';
    if (empty($errors)) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = uniqid('inv_', true) . '.' . $ext;
        $target = "$uploadDir/$newName";
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("INSERT INTO invoices (title, filename) VALUES (:title, :filename)");
            $stmt->execute(['title'=>$title, 'filename'=>$newName]);
            $success = true;
        } else {
            $errors[] = 'Impossibile salvare il file.';
        }
    }
}

$term = trim($_GET['term'] ?? '');
$invoices = [];
if ($term !== '') {
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE title LIKE :term ORDER BY created_at DESC");
    $stmt->execute(['term'=>"%{$term}%"]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Fatture — Alienity Portal</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-dark">
  <div class="dashboard-wrapper neon-card">
    <header class="neon-text dashboard-header">
      <img src="../assets/img/PortalLogo.png" alt="Logo" class="logo-large">
      <h2>Gestione Fatture</h2>
      <div class="header-controls">
        <a href="../dashboard.php" class="btn-neon small">Dashboard</a>
        <a href="../logout.php" class="btn-neon small">Logout</a>
      </div>
    </header>

    <section class="upload-section neon-card">
      <h3 class="neon-text">Carica Fattura</h3>
      <?php if ($success): ?>
        <p class="success-text">✅ Fattura caricata con successo!</p>
      <?php endif; ?>
      <?php foreach ($errors as $e): ?>
        <p class="error-text"><?php echo htmlspecialchars($e); ?></p>
      <?php endforeach; ?>
      <form method="post" enctype="multipart/form-data" class="login-form">
        <div class="input-group">
          <input type="text" name="title" required placeholder=" ">
          <label>Titolo Fattura</label>
        </div>
        <div class="input-group">
          <input type="file" name="invoice" accept="application/pdf" required>
        </div>
        <button type="submit" class="btn-neon">Carica</button>
      </form>
    </section>

    <section class="search-section neon-card">
      <h3 class="neon-text">Cerca Fatture</h3>
      <form method="get" class="login-form">
        <div class="input-group">
          <input type="text" name="term" value="<?php echo htmlspecialchars($term); ?>" placeholder=" ">
          <label>Parola chiave</label>
        </div>
        <button type="submit" class="btn-neon small">Cerca</button>
      </form>
      <?php if (!empty($invoices)): ?>
        <div class="invoice-list">
          <?php foreach ($invoices as $inv): ?>
            <div class="invoice-card neon-card">
              <div class="label"><?php echo htmlspecialchars($inv['title']); ?></div>
              <div class="created"><?php echo $inv['created_at']; ?></div>
              <a href="../uploads/invoices/<?php echo urlencode($inv['filename']); ?>" download class="btn-neon small">Scarica</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php elseif ($term !== ''): ?>
        <p class="text-light">Nessuna fattura trovata.</p>
      <?php endif; ?>
    </section>

  </div>
</body>
</html>
