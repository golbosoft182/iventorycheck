<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login();
$pdo = get_pdo();
$user = current_user();
$msg = '';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $err = 'Token tidak valid';
    } else {
        if (isset($_POST['action']) && $_POST['action'] === 'create') {
            $sku = trim($_POST['sku'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $barcode = trim($_POST['barcode'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            if ($sku && $name && $barcode) {
                try {
                    $stmt = $pdo->prepare('INSERT INTO items (sku,name,barcode,description) VALUES (?,?,?,?)');
                    $stmt->execute([$sku,$name,$barcode,$desc]);
                    $msg = 'Barang ditambahkan';
                } catch (Throwable $e) {
                    $err = 'Gagal menambah barang';
                }
            } else { $err = 'Isi SKU, Nama, Barcode'; }
        } elseif (isset($_POST['action']) && $_POST['action'] === 'update' && $user['role'] === 'superadmin') {
            $id = (int)($_POST['id'] ?? 0);
            $sku = trim($_POST['sku'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $barcode = trim($_POST['barcode'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            if ($id && $sku && $name && $barcode) {
                try {
                    $stmt = $pdo->prepare('UPDATE items SET sku=?, name=?, barcode=?, description=? WHERE id=?');
                    $stmt->execute([$sku,$name,$barcode,$desc,$id]);
                    $msg = 'Barang diupdate';
                } catch (Throwable $e) {
                    $err = 'Gagal update barang';
                }
            } else { $err = 'Data tidak lengkap'; }
        }
    }
}
$items = $pdo->query('SELECT * FROM items ORDER BY created_at DESC')->fetchAll();
?>
<?php include __DIR__ . '/../layout/header.php'; ?>
<div class="row">
  <div class="col-md-6">
    <h4><i class="fa-solid fa-box me-2"></i>Master Barang</h4>
    <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
    <form method="post" class="card card-body mb-3">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <input type="hidden" name="action" value="create">
      <div class="mb-2">
        <label class="form-label">SKU</label>
        <input name="sku" class="form-control" required>
      </div>
      <div class="mb-2">
        <label class="form-label">Nama Barang</label>
        <input name="name" class="form-control" required>
      </div>
      <div class="mb-2">
        <label class="form-label">Barcode</label>
        <input name="barcode" class="form-control" required>
      </div>
      <div class="mb-2">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" class="form-control"></textarea>
      </div>
      <button class="btn btn-primary" type="submit"><i class="fa-solid fa-plus me-1"></i>Tambah</button>
    </form>
  </div>
  <div class="col-md-6">
    <h4>Data Barang</h4>
    <table class="table table-striped table-sm">
      <thead><tr><th>SKU</th><th>Nama</th><th>Barcode</th><th class="table-actions">Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($items as $it): ?>
        <tr>
          <td><?php echo htmlspecialchars($it['sku']); ?></td>
          <td><?php echo htmlspecialchars($it['name']); ?></td>
          <td><?php echo htmlspecialchars($it['barcode']); ?></td>
          <td class="table-actions">
            <?php if ($user['role'] === 'superadmin'): ?>
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#edit<?php echo $it['id']; ?>"><i class="fa-solid fa-pen-to-square me-1"></i>Edit</button>
            <?php else: ?>
            <span class="text-muted">-</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php if ($user['role'] === 'superadmin'): ?>
        <div class="modal fade" id="edit<?php echo $it['id']; ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="post">
                <div class="modal-header"><h5 class="modal-title">Edit Barang</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                  <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="id" value="<?php echo $it['id']; ?>">
                  <div class="mb-2"><label class="form-label">SKU</label><input name="sku" class="form-control" value="<?php echo htmlspecialchars($it['sku']); ?>" required></div>
                  <div class="mb-2"><label class="form-label">Nama</label><input name="name" class="form-control" value="<?php echo htmlspecialchars($it['name']); ?>" required></div>
                  <div class="mb-2"><label class="form-label">Barcode</label><input name="barcode" class="form-control" value="<?php echo htmlspecialchars($it['barcode']); ?>" required></div>
                  <div class="mb-2"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control"><?php echo htmlspecialchars($it['description']); ?></textarea></div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary" type="submit">Simpan</button></div>
              </form>
            </div>
          </div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>