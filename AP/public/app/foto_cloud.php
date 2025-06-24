<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../config/DBConfig.php';
$pdo = DBConfig::getConnection();

$uploadDir = __DIR__ . '/../uploads/photos';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $file = $_FILES['photo'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = uniqid('photo_', true) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], "$uploadDir/$newName")) {
            $stmt = $pdo->prepare("INSERT INTO photos (filename) VALUES (:f)");
            $stmt->execute(['f' => $newName]);
        }
    }
}

$photos = $pdo->query("SELECT * FROM photos ORDER BY uploaded_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Foto Cloud — Alienity Portal</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .photos-grid {
      margin-top: 1.5rem;
      display: grid;
      grid-template-columns: repeat(auto-fit,minmax(150px,1fr));
      gap: 1rem;
    }
    .photos-grid img {
      width: 100%;
      border-radius: 6px;
    }
  </style>
</head>
<body class="bg-dark">
  <div class="dashboard-wrapper neon-card">
    <header class="neon-text dashboard-header">
      <img src="../assets/img/PortalLogo.png" alt="Logo" class="logo-large">
      <h2>Foto Cloud</h2>
      <div class="header-controls">
        <a href="../dashboard.php" class="btn-neon small">Dashboard</a>
        <a href="../logout.php" class="btn-neon small">Logout</a>
      </div>
    </header>

    <section class="upload-section neon-card">
      <h3 class="neon-text">Carica Foto</h3>
      <form method="post" enctype="multipart/form-data" class="login-form">
        <input type="file" name="photo" accept="image/*" required>
        <button type="submit" class="btn-neon">Carica</button>
      </form>
    </section>

    <?php if ($photos): ?>
    <section class="neon-card photos-grid">
      <?php foreach ($photos as $p): ?>
        <img src="../uploads/photos/<?php echo urlencode($p['filename']); ?>" alt="foto">
      <?php endforeach; ?>
    </section>
    <?php endif; ?>
  </div>
</body>
</html>
