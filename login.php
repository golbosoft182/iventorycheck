<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/config.php';
if (is_logged_in()) {
    header('Location: ' . APP_BASE . '/dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token tidak valid';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$username || !$password) {
            $error = 'Masukkan username dan password';
        } else {
            if (login_user($username, $password)) {
                header('Location: ' . APP_BASE . '/dashboard.php');
                exit;
            } else {
                $error = 'Login gagal';
            }
        }
    }
}
?>
<?php include __DIR__ . '/layout/header.php'; ?>
<div class="row justify-content-center">
  <div class="col-md-4">
    <h3 class="mb-3">Login</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="d-flex justify-content-between">
        <button type="submit" class="btn btn-primary">Masuk</button>
        <a href="setup.php" class="btn btn-outline-secondary">Setup Awal</a>
      </div>
    </form>
  </div>
  
</div>
<?php include __DIR__ . '/layout/footer.php'; ?>