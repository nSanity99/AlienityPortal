<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}
$configPath = dirname(__DIR__) . '/config/DBConfig.php';
if (!file_exists($configPath)) {
    $configPath = __DIR__ . '/../config/DBConfig.php';
}
require_once $configPath;
$pdo = DBConfig::getConnection();

// SQL for invoices table creation if needed...

$errors = [];
$success = false;

// Ensure uploads dir
$uploadDir = __DIR__ . '/uploads/invoices';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Handle file upload
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

// Search handling
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
  <title>Pagamenti — Alienity Portal</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Pagamenti — Alienity Portal</title>
  <style>
    :root {
      --neon-pink: #ff2d95;
      --neon-blue: #00d0ff;
      --neon-purple: #9c27b0;
      --bg-dark: #0d0d0d;
      --text-light: #eee;
    }
    body.bg-dark {
      margin: 0; padding: 0;
      background: var(--bg-dark);
      font-family: 'Segoe UI', sans-serif;
      color: var(--text-light);
    }
    .neon-card {
      background: rgba(15,15,15,0.85);
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 0 10px rgba(0,0,0,0.7);
      position: relative; overflow: hidden;
    }
    .neon-card::before {
      content: '';
      position: absolute; top: -50%; left: -50%;
      width: 200%; height: 200%;
      background: conic-gradient(
        from 0deg,
        var(--neon-pink),
        var(--neon-purple),
        var(--neon-blue),
        var(--neon-pink)
      );
      animation: rotate 4s linear infinite;
      z-index: -1;
    }
    @keyframes rotate {
      from { transform: rotate(0deg); }
      to   { transform: rotate(360deg); }
    }
    .dashboard-wrapper { max-width: 900px; margin: 2rem auto; }
    .dashboard-header {
      display: flex; align-items: center;
      justify-content: space-between; gap: 1rem;
      margin-bottom: 2rem;
    }
    .neon-text {
      text-shadow:
        0 0 4px var(--neon-pink),
        0 0 8px var(--neon-purple),
        0 0 12px var(--neon-blue);
    }
    .header-controls { display: flex; gap: .75rem; }
    .btn-neon {
      padding: .75rem 1rem;
      background: linear-gradient(45deg, var(--neon-pink), var(--neon-blue));
      border: none; border-radius: 6px;
      color: var(--text-light); text-transform: uppercase;
      box-shadow: 0 0 8px var(--neon-pink), 0 0 16px var(--neon-blue);
      cursor: pointer; transition: transform .2s, box-shadow .2s;
    }
    .btn-neon:hover {
      transform: scale(1.05);
      box-shadow: 0 0 12px var(--neon-pink), 0 0 24px var(--neon-blue);
    }
    .btn-neon.small { padding: .4rem .8rem; font-size: .9rem; }
    .upload-section, .search-section { margin-top: 2rem; }
    .login-form { display: flex; flex-direction: column; gap: 1rem; }
    .input-group { position: relative; }
    .input-group input {
      width: 100%; padding: .75rem;
      background: transparent; border: none;
      border-bottom: 2px solid var(--text-light);
      color: var(--text-light);
      transition: border-color .3s;
    }
    .input-group label {
      position: absolute; top: 50%; left: .5rem;
      transform: translateY(-50%);
      pointer-events: none;
      color: var(--text-light);
      transition: top .3s, font-size .3s;
    }
    .input-group input:focus,
    .input-group input:not(:placeholder-shown) {
      border-color: var(--neon-pink);
    }
    .input-group input:focus + label,
    .input-group input:not(:placeholder-shown) + label {
      top: -0.5rem; font-size: .75rem; color: var(--neon-pink);
    }
    .invoice-list {
      display: grid;
      grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
      gap: 1rem; margin-top: 1.5rem;
    }
    .invoice-card {
      background: rgba(15,15,15,0.6);
      border-radius: 8px; padding: 1rem;
      text-align: center;
      transition: transform .2s, box-shadow .2s;
    }
    .invoice-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 0 10px var(--neon-blue),0 0 20px var(--neon-pink);
    }
    .invoice-card .label { font-weight: bold; margin-bottom: .5rem; }
    .invoice-card .created {
      font-size: .8rem; margin-bottom: .75rem;
      color: var(--text-light);
    }
  </style>
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
              <a href="uploads/invoices/<?php echo urlencode($inv['filename']); ?>" download class="btn-neon small">Scarica</a>
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
