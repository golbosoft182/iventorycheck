<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$pdo = get_pdo();
$packs = $pdo->query("SELECT p.id, p.packing_date, c.name AS company, d.name AS destination, w.name AS warehouse,
  COALESCE((SELECT SUM(planned_qty) FROM packing_items WHERE packing_id=p.id),0) AS planned,
  COALESCE((SELECT SUM(scanned_qty) FROM packing_items WHERE packing_id=p.id),0) AS scanned
  FROM packings p
  JOIN companies c ON p.company_id=c.id
  JOIN destinations d ON p.destination_id=d.id
  JOIN warehouses w ON p.warehouse_id=w.id
  ORDER BY p.id DESC LIMIT 20")->fetchAll();
?>
<?php include __DIR__ . '/layout/header.php'; ?>
<div class="row">
  <div class="col-12">
    <h3><i class="fa-solid fa-gauge-high me-2"></i>Dashboard</h3>
    <p>Gunakan menu untuk mengelola data master, membuat packing, dan scan barcode.</p>
  </div>
</div>
<div class="row g-3 mt-3">
  <div class="col-12">
    <div class="card desktop-card">
      <div class="card-header"><i class="fa-solid fa-list me-2"></i>Daftar Packing Terbaru</div>
      <div class="card-body">
        <div class="table-responsive desktop-grid">
          <table class="table table-hover table-sm mb-0">
            <thead class="grid-header"><tr><th>ID</th><th>Tanggal</th><th>Perusahaan</th><th>Gudang</th><th>Tujuan</th><th>Rencana</th><th>Scan</th><th>Status</th><th class="table-actions">Aksi</th></tr></thead>
            <tbody>
            <?php foreach($packs as $p): $planned=(int)$p['planned']; $scanned=(int)$p['scanned']; $pct=$planned?min(100,round($scanned*100/$planned)):0; ?>
              <tr>
                <td><?php echo (int)$p['id']; ?></td>
                <td><?php echo htmlspecialchars($p['packing_date']); ?></td>
                <td><?php echo htmlspecialchars($p['company']); ?></td>
                <td><?php echo htmlspecialchars($p['warehouse']); ?></td>
                <td><?php echo htmlspecialchars($p['destination']); ?></td>
                <td><?php echo $planned; ?></td>
                <td><?php echo $scanned; ?></td>
                <td style="width:160px">
                  <div class="progress progress-sm">
                    <div class="progress-bar" role="progressbar" style="width: <?php echo $pct; ?>%" aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </td>
                <td class="table-actions">
                  <a class="btn btn-sm btn-outline-primary" href="<?php echo APP_URL; ?>/pages/packings_manage.php?id=<?php echo (int)$p['id']; ?>"><i class="fa-solid fa-gear me-1"></i>Manage</a>
                  <a class="btn btn-sm btn-success" href="<?php echo APP_URL; ?>/pages/scan.php?id=<?php echo (int)$p['id']; ?>"><i class="fa-solid fa-camera me-1"></i>Scan</a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row g-3 mt-2">
  <div class="col-md-3">
    <a href="<?php echo APP_URL; ?>/pages/items.php" class="btn btn-outline-primary w-100"><i class="fa-solid fa-box me-1"></i>Master Barang</a>
  </div>
  <div class="col-md-3">
    <a href="<?php echo APP_URL; ?>/pages/companies.php" class="btn btn-outline-primary w-100"><i class="fa-solid fa-building me-1"></i>Perusahaan</a>
  </div>
  <div class="col-md-3">
    <a href="<?php echo APP_URL; ?>/pages/warehouses.php" class="btn btn-outline-primary w-100"><i class="fa-solid fa-warehouse me-1"></i>Gudang</a>
  </div>
  <div class="col-md-3">
    <a href="<?php echo APP_URL; ?>/pages/destinations.php" class="btn btn-outline-primary w-100"><i class="fa-solid fa-location-dot me-1"></i>Tujuan</a>
  </div>
  <div class="col-md-3">
    <a href="<?php echo APP_URL; ?>/pages/packings_create.php" class="btn btn-success w-100"><i class="fa-solid fa-dolly me-1"></i>Buat Packing</a>
  </div>
  <div class="col-md-3">
    <a href="<?php echo APP_URL; ?>/pages/barcodes.php" class="btn btn-secondary w-100"><i class="fa-solid fa-barcode me-1"></i>Generate Barcode</a>
  </div>
  <?php $u = current_user(); if ($u && $u['role']==='superadmin'): ?>
  <div class="col-md-3">
    <a href="<?php echo APP_URL; ?>/pages/seed_dummy.php" class="btn btn-warning w-100"><i class="fa-solid fa-flask me-1"></i>Tambah Data Dummy</a>
  </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/layout/footer.php'; ?>