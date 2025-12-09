<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
$user = current_user();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo APP_NAME; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.6.0/css/all.min.css" rel="stylesheet">
  <base href="<?php echo APP_URL; ?>/">
  <link href="<?php echo APP_URL; ?>/assets/css/styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?php echo APP_URL; ?>/dashboard.php"><i class="fa-solid fa-clipboard-check me-2"></i><?php echo APP_NAME; ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>/pages/items.php"><i class="fa-solid fa-box me-1"></i>Master Barang</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>/pages/companies.php"><i class="fa-solid fa-building me-1"></i>Perusahaan</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>/pages/warehouses.php"><i class="fa-solid fa-warehouse me-1"></i>Gudang</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>/pages/destinations.php"><i class="fa-solid fa-location-dot me-1"></i>Tujuan</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>/pages/packings_create.php"><i class="fa-solid fa-dolly me-1"></i>Buat Packing</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>/pages/barcodes.php"><i class="fa-solid fa-barcode me-1"></i>Generate Barcode</a></li>
      </ul>
      <ul class="navbar-nav">
        <?php if ($user): ?>
        <li class="nav-item"><span class="navbar-text d-block"><i class="fa-solid fa-user me-1"></i><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</span></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>/logout.php"><i class="fa-solid fa-right-from-bracket me-1"></i>Logout</a></li>
        <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>/login.php"><i class="fa-solid fa-right-to-bracket me-1"></i>Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4">