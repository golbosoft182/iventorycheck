<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
$packing_id = (int)($input['packing_id'] ?? 0);
$barcode = trim($input['barcode'] ?? '');
$barcode = preg_replace('/[\x00-\x1F\x7F]/', '', $barcode);
$barcode = preg_replace('/\s+/', '', $barcode);
if (!$packing_id || !$barcode) { echo json_encode(['ok'=>false,'error'=>'Data tidak lengkap']); exit; }
$pdo = get_pdo();
$stmt = $pdo->prepare('SELECT pi.id, pi.planned_qty, pi.scanned_qty, i.barcode FROM packing_items pi JOIN items i ON pi.item_id=i.id WHERE pi.packing_id=? AND UPPER(i.barcode)=UPPER(?) LIMIT 1');
$stmt->execute([$packing_id, $barcode]);
$pi = $stmt->fetch();
if (!$pi) { echo json_encode(['ok'=>false,'error'=>'Barcode tidak terdaftar']); exit; }
if ($pi['scanned_qty'] >= $pi['planned_qty']) { echo json_encode(['ok'=>false,'error'=>'Qty sudah terpenuhi','barcode'=>$barcode,'scanned_qty'=>$pi['scanned_qty']]); exit; }
$newQty = $pi['scanned_qty'] + 1;
$upd = $pdo->prepare('UPDATE packing_items SET scanned_qty=? WHERE id=?');
$upd->execute([$newQty, $pi['id']]);
echo json_encode(['ok'=>true,'message'=>'Scan tercatat','barcode'=>$pi['barcode'],'scanned_qty'=>$newQty]);
?>