<?php
require_once __DIR__ . '/../includes/db.php';
echo "InventoryCheck Seeder\n";
$pdo = get_pdo();
try {
    $pdo->query('SELECT 1 FROM users LIMIT 1');
    echo "Schema exists.\n";
} catch (Throwable $e) {
    echo "Applying schema...\n";
    $sql = file_get_contents(__DIR__ . '/../db/init_db.sql');
    $pdo->exec($sql);
    echo "Schema applied.\n";
}
echo "Seeding dummy data...\n";
$seed = file_get_contents(__DIR__ . '/../db/seed_dummy.sql');
$pdo->exec($seed);
echo "Seed complete.\n";
?>
