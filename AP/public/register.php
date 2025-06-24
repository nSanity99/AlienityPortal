<?php session_start();
require_once __DIR__ . '/../config/DBConfig.php';

// Se già loggato, reindirizza
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success = isset($_GET['registered']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['confirm_password'] ?? '';

    if (!$user || !$pass || !$pass2) {
        $errors[] = 'Tutti i campi sono obbligatori.';
    } elseif ($pass !== $pass2) {
        $errors[] = 'Le password non corrispondono.';
    } else {
        // Verifica unicità username
        $pdo = DBConfig::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :u");
        $stmt->execute(['u' => $user]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Username già in uso.';
        } else {
            // Inserisci utente
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:u, :h)");
            $ins->execute(['u' => $user, 'h' => $hash]);
            header('Location: index.php?registered=1');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Registrazione — Alienity Portal</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="login-wrapper">
    <div class="login-card neon-card">
      <img src="assets/img/PortalLogo.png" alt="Alienity Portal" class="logo">
      <h1 class="neon-text">Crea il tuo account</h1>

      <?php if ($success): ?>
        <p class="success-text">✅ Registrazione avvenuta con successo! Effettua il login.</p>
      <?php endif; ?>
      <?php if ($errors): ?>
        <?php foreach ($errors as $e): ?>
          <p class="error-text"><?php echo htmlspecialchars($e); ?></p>
        <?php endforeach; ?>
      <?php endif; ?>

      <form action="register.php" method="post" class="login-form">
        <div class="input-group">
          <input type="text" name="username" value="<?php echo htmlspecialchars($user ?? ''); ?>" required placeholder=" ">
          <label>Username</label>
        </div>
        <div class="input-group">
          <input type="password" name="password" required placeholder=" ">
          <label>Password</label>
        </div>
        <div class="input-group">
          <input type="password" name="confirm_password" required placeholder=" ">
          <label>Conferma Password</label>
        </div>
        <button type="submit" class="btn-neon">Registrati</button>
      </form>

      <p class="mt-4"><a href="index.php" class="link-light">Hai già un account? Accedi</a></p>
    </div>
  </div>
</body>
</html>