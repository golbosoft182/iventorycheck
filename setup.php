<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
$pdo = get_pdo();
$schema_applied = false;
try {
    $pdo->query('SELECT 1 FROM users LIMIT 1');
} catch (Throwable $e) {
    $sql = file_get_contents(__DIR__ . '/db/init_db.sql');
    $pdo->exec($sql);
    $schema_applied = true;
}
$stmt = $pdo->query('SELECT COUNT(*) AS c FROM users');
$count = (int)$stmt->fetch()['c'];
$created = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token tidak valid';
    } else {
        $su_user = trim($_POST['su_username'] ?? '');
        $su_pass = $_POST['su_password'] ?? '';
        $ad_user = trim($_POST['ad_username'] ?? '');
        $ad_pass = $_POST['ad_password'] ?? '';
        if (!$su_user || !$su_pass) {
            $error = 'Isi superadmin username dan password';
        } else {
            $pdo->beginTransaction();
            $su_hash = password_hash($su_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username,password_hash,role) VALUES (?,?,?)');
            $stmt->execute([$su_user, $su_hash, 'superadmin']);
            if ($ad_user && $ad_pass) {
                $ad_hash = password_hash($ad_pass, PASSWORD_DEFAULT);
                $stmt->execute([$ad_user, $ad_hash, 'admin']);
            }
            $pdo->commit();
            $created = true;
        }
    }
}
?>
<?php include __DIR__ . '/layout/header.php'; ?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <h3 class="mb-3">Setup Awal</h3>
    <?php if ($schema_applied): ?><div class="alert alert-info">Schema database dibuat.</div><?php endif; ?>
    <?php if ($count > 0 && !$created): ?>
      <div class="alert alert-warning">User sudah ada. Silakan login.</div>
      <a href="login.php" class="btn btn-primary">Ke Login</a>
    <?php else: ?>
      <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
      <?php if ($created): ?>
        <div class="alert alert-success">User dibuat. Silakan login.</div>
        <a href="login.php" class="btn btn-primary">Ke Login</a>
      <?php else: ?>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          <div class="card mb-3">
            <div class="card-body">
              <h5 class="card-title">Superadmin</h5>
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="su_username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="su_password" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="card mb-3">
            <div class="card-body">
              <h5 class="card-title">Admin (opsional)</h5>
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="ad_username" class="form-control">
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="ad_password" class="form-control">
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-success">Buat User</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/layout/footer.php'; ?>