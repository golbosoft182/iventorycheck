<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$pdo = get_pdo();
$items = $pdo->query('SELECT id, sku, name, barcode FROM items ORDER BY name')->fetchAll();
?>
<?php include __DIR__ . '/../layout/header.php'; ?>
<div class="row">
  <div class="col-12">
    <h4><i class="fa-solid fa-barcode me-2"></i>Generate Barcode</h4>
    <p>Pilih item untuk generate barcode dan unduh.</p>
  </div>
  <?php foreach($items as $it): ?>
  <div class="col-md-4 mb-3">
    <div class="card">
      <div class="card-body text-center">
        <div><strong><?php echo htmlspecialchars($it['name']); ?></strong></div>
        <svg id="svg<?php echo $it['id']; ?>"></svg>
        <div class="mt-2">
          <button class="btn btn-sm btn-secondary" onclick="downloadSvg('svg<?php echo $it['id']; ?>','<?php echo htmlspecialchars($it['sku']); ?>')"><i class="fa-solid fa-download me-1"></i>Unduh</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function(){ JsBarcode('#svg<?php echo $it['id']; ?>', '<?php echo htmlspecialchars($it['barcode']); ?>', {format:'CODE128', width:2, height:60, displayValue:true}); });
  </script>
  <?php endforeach; ?>
</div>
<script src="<?php echo APP_URL; ?>/assets/vendor/jsbarcode.min.js"></script>
<script>
function downloadSvg(id, name) {
  const svg = document.getElementById(id);
  const data = new XMLSerializer().serializeToString(svg);
  const blob = new Blob([data], {type: 'image/svg+xml'});
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = name + '.svg'; a.click();
  URL.revokeObjectURL(url);
}
</script>
<?php include __DIR__ . '/../layout/footer.php'; ?>