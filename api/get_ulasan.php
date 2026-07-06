<?php
header('Content-Type: application/json');
require 'db.php';

$destinasiId = isset($_GET['destinasiId']) ? (int) $_GET['destinasiId'] : 0;
if ($destinasiId <= 0) {
    echo json_encode(['success' => false, 'message' => 'destinasiId wajib diisi.']);
    exit;
}

$stmt = $pdo->prepare('SELECT nama_pengunjung, bintang, komentar, dibuat_pada
                        FROM ulasan WHERE destinasi_id = ? ORDER BY dibuat_pada DESC');
$stmt->execute([$destinasiId]);
$rows = $stmt->fetchAll();

$total = count($rows);
$avg = 0;
if ($total > 0) {
    $sum = 0;
    foreach ($rows as $r) {
        $sum += (int) $r['bintang'];
    }
    $avg = round($sum / $total, 1);
}

echo json_encode([
    'success' => true,
    'data' => $rows,
    'total' => $total,
    'avg' => $avg,
]);
