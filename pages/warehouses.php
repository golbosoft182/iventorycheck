<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login();
$pdo = get_pdo();
$user = current_user();
$msg='';$err='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) { $err='Token tidak valid'; }
    else {
        if (isset($_POST['action']) && $_POST['action']==='create') {
            $name = trim($_POST['name'] ?? '');
            if ($name) {
                try { $pdo->prepare('INSERT INTO warehouses (name) VALUES (?)')->execute([$name]); $msg='Gudang ditambah'; } catch (Throwable $e) { $err='Gagal'; }
            } else { $err='Isi nama'; }
        } elseif (isset($_POST['action']) && $_POST['action']==='update' && $user['role']==='superadmin') {
            $id=(int)($_POST['id']??0); $name=trim($_POST['name']??'');
            if ($id && $name) { try { $pdo->prepare('UPDATE warehouses SET name=? WHERE id=?')->execute([$name,$id]); $msg='Diupdate'; } catch (Throwable $e) { $err='Gagal'; } } else { $err='Data tidak lengkap'; }
        }
    }
}
$rows = $pdo->query('SELECT * FROM warehouses ORDER BY name')->fetchAll();
?>
<?php include __DIR__ . '/../layout/header.php'; ?>
<div class="row">
  <div class="col-md-6">
    <h4><i class="fa-solid fa-warehouse me-2"></i>Gudang</h4>
    <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
    <form method="post" class="card card-body mb-3">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <input type="hidden" name="action" value="create">
      <div class="mb-2"><label class="form-label">Nama</label><input name="name" class="form-control" required></div>
      <button class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Tambah</button>
    </form>
  </div>
  <div class="col-md-6">
    <h4>Data</h4>
    <table class="table table-striped table-sm"><thead><tr><th>Nama</th><th class="table-actions">Aksi</th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?>
      <tr><td><?php echo htmlspecialchars($r['name']); ?></td><td class="table-actions">
        <?php if ($user['role']==='superadmin'): ?>
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#m<?php echo $r['id']; ?>"><i class="fa-solid fa-pen-to-square me-1"></i>Edit</button>
        <?php else: ?><span class="text-muted">-</span><?php endif; ?>
      </td></tr>
      <?php if ($user['role']==='superadmin'): ?>
      <div class="modal fade" id="m<?php echo $r['id']; ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <form method="post"><div class="modal-header"><h5 class="modal-title">Edit</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
          <div class="mb-2"><label class="form-label">Nama</label><input name="name" class="form-control" value="<?php echo htmlspecialchars($r['name']); ?>" required></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
        </form>
      </div></div></div>
      <?php endif; ?>
    <?php endforeach; ?>
    </tbody></table>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>