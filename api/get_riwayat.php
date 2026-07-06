<?php
header('Content-Type: application/json');
require 'db.php';

$sesiId = isset($_GET['sesiId']) ? trim($_GET['sesiId']) : '';
if ($sesiId === '') {
    echo json_encode(['success' => false, 'message' => 'sesiId wajib diisi.']);
    exit;
}

$sql = "SELECT p.peran, p.isi_pesan, p.dikirim_pada
        FROM pesan_chat p
        JOIN chat_sesi s ON s.id = p.sesi_id
        WHERE s.sesi_id = ?
        ORDER BY p.dikirim_pada ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$sesiId]);
$rows = $stmt->fetchAll();

echo json_encode(['success' => true, 'data' => $rows]);
