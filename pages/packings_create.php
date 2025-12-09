<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login();
$pdo = get_pdo();
$user = current_user();
$companies = $pdo->query('SELECT id,name FROM companies ORDER BY name')->fetchAll();
$warehouses = $pdo->query('SELECT id,name FROM warehouses ORDER BY name')->fetchAll();
$destinations = $pdo->query('SELECT id,name FROM destinations ORDER BY name')->fetchAll();
$users = $pdo->query('SELECT id,username,role FROM users ORDER BY username')->fetchAll();
$msg='';$err='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) { $err='Token tidak valid'; }
    else {
        $company_id=(int)($_POST['company_id']??0);
        $warehouse_id=(int)($_POST['warehouse_id']??0);
        $destination_id=(int)($_POST['destination_id']??0);
        $checker_id=(int)($_POST['checker_id']??0);
        $packer_id=(int)($_POST['packer_id']??0);
        $date = trim($_POST['packing_date'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        if ($company_id && $warehouse_id && $destination_id && $checker_id && $packer_id && $date) {
            try {
                $stmt = $pdo->prepare('INSERT INTO packings (packing_date,company_id,destination_id,warehouse_id,checker_id,packer_id,notes) VALUES (?,?,?,?,?,?,?)');
                $stmt->execute([$date,$company_id,$destination_id,$warehouse_id,$checker_id,$packer_id,$notes]);
                $pid = $pdo->lastInsertId();
                header('Location: packings_manage.php?id=' . $pid);
                exit;
            } catch (Throwable $e) { $err='Gagal membuat packing'; }
        } else { $err='Isi semua field'; }
    }
}
?>
<?php include __DIR__ . '/../layout/header.php'; ?>
<div class="row">
  <div class="col-md-8">
    <h4><i class="fa-solid fa-dolly me-2"></i>Buat Packing</h4>
    <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
    <form method="post" class="card card-body">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Perusahaan</label><select name="company_id" class="form-select" required><?php foreach($companies as $c){echo '<option value="'.$c['id'].'">'.htmlspecialchars($c['name']).'</option>'; } ?></select></div>
        <div class="col-md-6"><label class="form-label">Gudang</label><select name="warehouse_id" class="form-select" required><?php foreach($warehouses as $w){echo '<option value="'.$w['id'].'">'.htmlspecialchars($w['name']).'</option>'; } ?></select></div>
        <div class="col-md-6"><label class="form-label">Tujuan</label><select name="destination_id" class="form-select" required><?php foreach($destinations as $d){echo '<option value="'.$d['id'].'">'.htmlspecialchars($d['name']).'</option>'; } ?></select></div>
        <div class="col-md-6"><label class="form-label">Tanggal</label><input type="datetime-local" name="packing_date" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Yang mengecek</label><select name="checker_id" class="form-select" required><?php foreach($users as $u){echo '<option value="'.$u['id'].'">'.htmlspecialchars($u['username']).' ('.htmlspecialchars($u['role']).')</option>'; } ?></select></div>
        <div class="col-md-6"><label class="form-label">Yang packing</label><select name="packer_id" class="form-select" required><?php foreach($users as $u){echo '<option value="'.$u['id'].'">'.htmlspecialchars($u['username']).' ('.htmlspecialchars($u['role']).')</option>'; } ?></select></div>
        <div class="col-12"><label class="form-label">Catatan</label><textarea name="notes" class="form-control"></textarea></div>
      </div>
      <div class="mt-3"><button class="btn btn-success"><i class="fa-solid fa-check me-1"></i>Buat</button></div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>