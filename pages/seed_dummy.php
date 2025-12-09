<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login();
$u = current_user();
if (!$u || $u['role'] !== 'superadmin') { http_response_code(403); echo 'Forbidden'; exit; }
$pdo = get_pdo();
$msg='';$err='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) { $err='Token tidak valid'; }
    else {
        try {
            $pdo->beginTransaction();
            $pdo->exec("INSERT IGNORE INTO companies (name) VALUES ('PT Alpha'),('PT Beta'),('PT Gamma')");
            $pdo->exec("INSERT IGNORE INTO warehouses (name) VALUES ('Gudang Jakarta'),('Gudang Surabaya')");
            $pdo->exec("INSERT IGNORE INTO destinations (name) VALUES ('Kalimantan'),('Sumatera'),('Sulawesi')");
            $stmt = $pdo->prepare('INSERT IGNORE INTO items (sku,name,barcode,description) VALUES (?,?,?,?)');
            for ($i=1;$i<=10;$i++) {
                $sku = sprintf('PC-%03d',$i);
                $name = 'PC ' . sprintf('%03d',$i);
                $barcode = 'PC' . sprintf('%03d',$i);
                $desc = 'Unit PC ' . $i;
                $stmt->execute([$sku,$name,$barcode,$desc]);
            }
            $pdo->commit();
            $msg='Data dummy ditambahkan';
        } catch (Throwable $e) { $pdo->rollBack(); $err='Gagal menambah data'; }
    }
}
?>
<?php include __DIR__ . '/../layout/header.php'; ?>
<div class="row">
  <div class="col-md-6">
    <h4><i class="fa-solid fa-flask me-2"></i>Tambah Data Dummy</h4>
    <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
    <form method="post" class="card card-body">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <p>Menambahkan perusahaan, gudang, tujuan, dan 10 item PC.</p>
      <button class="btn btn-warning"><i class="fa-solid fa-flask me-1"></i>Jalankan Seed</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>