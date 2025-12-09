<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login();
$pdo = get_pdo();
$user = current_user();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: packings_create.php'); exit; }
$packing = $pdo->prepare('SELECT p.*, c.name AS company, w.name AS warehouse, d.name AS destination FROM packings p JOIN companies c ON p.company_id=c.id JOIN warehouses w ON p.warehouse_id=w.id JOIN destinations d ON p.destination_id=d.id WHERE p.id=?');
$packing->execute([$id]);
$packing = $packing->fetch();
if (!$packing) { header('Location: packings_create.php'); exit; }
$msg='';$err='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) { $err='Token tidak valid'; }
    else {
        if (isset($_POST['action']) && $_POST['action']==='add_item') {
            $item_id=(int)($_POST['item_id']??0);
            $qty=(int)($_POST['planned_qty']??0);
            if ($item_id && $qty>0) {
                try {
                    $stmt=$pdo->prepare('INSERT INTO packing_items (packing_id,item_id,planned_qty) VALUES (?,?,?)');
                    $stmt->execute([$id,$item_id,$qty]);
                    $msg='Item ditambahkan';
                } catch (Throwable $e) { $err='Gagal menambah item'; }
            } else { $err='Pilih item dan qty'; }
        } elseif (isset($_POST['action']) && $_POST['action']==='update_qty' && $user['role']==='superadmin') {
            $pi_id=(int)($_POST['pi_id']??0); $qty=(int)($_POST['planned_qty']??0);
            if ($pi_id && $qty>0) { try { $pdo->prepare('UPDATE packing_items SET planned_qty=? WHERE id=?')->execute([$qty,$pi_id]); $msg='Qty diupdate'; } catch (Throwable $e) { $err='Gagal'; } } else { $err='Data tidak lengkap'; }
        }
    }
}
$items = $pdo->query('SELECT id, name FROM items ORDER BY name')->fetchAll();
$packing_items = $pdo->prepare('SELECT pi.*, i.name, i.barcode FROM packing_items pi JOIN items i ON pi.item_id=i.id WHERE pi.packing_id=? ORDER BY pi.id');
$packing_items->execute([$id]);
$packing_items = $packing_items->fetchAll();
?>
<?php include __DIR__ . '/../layout/header.php'; ?>
<div class="row">
  <div class="col-12">
    <h4><i class="fa-solid fa-boxes-packing me-2"></i>Manage Packing #<?php echo $id; ?></h4>
    <div class="mb-2">Perusahaan: <?php echo htmlspecialchars($packing['company']); ?> | Gudang: <?php echo htmlspecialchars($packing['warehouse']); ?> | Tujuan: <?php echo htmlspecialchars($packing['destination']); ?> | Tanggal: <?php echo htmlspecialchars($packing['packing_date']); ?></div>
    <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
  </div>
  <div class="col-md-6">
    <form method="post" class="card card-body mb-3">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <input type="hidden" name="action" value="add_item">
      <div class="mb-2"><label class="form-label">Item</label><select name="item_id" class="form-select" required><?php foreach($items as $it){echo '<option value="'.$it['id'].'">'.htmlspecialchars($it['name']).'</option>';} ?></select></div>
      <div class="mb-2"><label class="form-label">Qty Rencana</label><input type="number" min="1" name="planned_qty" class="form-control" required></div>
      <button class="btn btn-primary">Tambah Item</button>
    </form>
  </div>
  <div class="col-md-6">
    <div class="d-flex justify-content-between align-items-center">
      <h5><i class="fa-solid fa-list me-2"></i>Item Packing</h5>
      <a class="btn btn-success btn-sm" href="<?php echo APP_URL; ?>/pages/scan.php?id=<?php echo $id; ?>"><i class="fa-solid fa-camera me-1"></i>Mulai Scan</a>
    </div>
    <table class="table table-striped table-sm mt-2"><thead><tr><th>Nama</th><th>Barcode</th><th>Rencana</th><th>Scan</th><th class="table-actions">Aksi</th></tr></thead><tbody>
      <?php foreach($packing_items as $pi): ?>
      <tr>
        <td><?php echo htmlspecialchars($pi['name']); ?></td>
        <td><code><?php echo htmlspecialchars($pi['barcode']); ?></code></td>
        <td><?php echo (int)$pi['planned_qty']; ?></td>
        <td><?php echo (int)$pi['scanned_qty']; ?></td>
        <td class="table-actions">
          <?php if ($user['role']==='superadmin'): ?>
          <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#q<?php echo $pi['id']; ?>"><i class="fa-solid fa-pencil me-1"></i>Qty</button>
          <?php else: ?><span class="text-muted">-</span><?php endif; ?>
        </td>
      </tr>
      <?php if ($user['role']==='superadmin'): ?>
      <div class="modal fade" id="q<?php echo $pi['id']; ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <form method="post"><div class="modal-header"><h5 class="modal-title">Update Qty</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          <input type="hidden" name="action" value="update_qty">
          <input type="hidden" name="pi_id" value="<?php echo $pi['id']; ?>">
          <div class="mb-2"><label class="form-label">Qty Rencana</label><input type="number" min="1" name="planned_qty" class="form-control" value="<?php echo (int)$pi['planned_qty']; ?>" required></div>
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