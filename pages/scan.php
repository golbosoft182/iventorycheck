<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$pdo = get_pdo();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: packings_create.php'); exit; }
$packing = $pdo->prepare('SELECT id, packing_date FROM packings WHERE id=?');
$packing->execute([$id]);
$packing = $packing->fetch();
if (!$packing) { header('Location: packings_create.php'); exit; }
$items = $pdo->prepare('SELECT pi.id, i.name, i.barcode, pi.planned_qty, pi.scanned_qty FROM packing_items pi JOIN items i ON pi.item_id=i.id WHERE pi.packing_id=? ORDER BY i.name');
$items->execute([$id]);
$items = $items->fetchAll();
?>
<?php include __DIR__ . '/../layout/header.php'; ?>
<div class="row">
  <div class="col-md-6">
    <h4><i class="fa-solid fa-camera me-2"></i>Scan Packing #<?php echo $id; ?></h4>
    <div id="reader" style="width:100%"></div>
    <div class="mt-2">
      <div class="input-group">
        <span class="input-group-text">Manual</span>
        <input id="manualBarcode" class="form-control" placeholder="Masukkan barcode" autofocus>
        <button id="manualSubmit" class="btn btn-outline-primary"><i class="fa-solid fa-paper-plane me-1"></i>Submit</button>
        <button id="connectScanner" class="btn btn-outline-success"><i class="fa-solid fa-plug me-1"></i>Connect Scanner</button>
      </div>
    </div>
    <div class="mt-3" id="scanMessage"></div>
  </div>
  <div class="col-md-6">
    <h5><i class="fa-solid fa-chart-simple me-2"></i>Progres</h5>
    <table class="table table-striped table-sm"><thead><tr><th>Nama</th><th>Barcode</th><th>Rencana</th><th>Scan</th></tr></thead><tbody id="progressBody">
      <?php foreach($items as $it): ?>
      <tr data-barcode="<?php echo htmlspecialchars($it['barcode']); ?>">
        <td><?php echo htmlspecialchars($it['name']); ?></td>
        <td><code><?php echo htmlspecialchars($it['barcode']); ?></code></td>
        <td><?php echo (int)$it['planned_qty']; ?></td>
        <td class="scanned"><?php echo (int)$it['scanned_qty']; ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody></table>
  </div>
</div>
<script src="<?php echo APP_URL; ?>/assets/vendor/html5-qrcode.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.10/dist/html5-qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@latest/dist/html5-qrcode.min.js"></script>
<script>
const packingId = <?php echo (int)$id; ?>;
const API_URL = '<?php echo APP_URL; ?>/api/scan.php';
</script>
<script src="<?php echo APP_URL; ?>/assets/js/scan.js"></script>
<?php include __DIR__ . '/../layout/footer.php'; ?>