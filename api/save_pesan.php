<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$sesiId = isset($data['sesiId']) ? trim($data['sesiId']) : '';
$peran = isset($data['peran']) ? $data['peran'] : '';
$isiPesan = isset($data['isiPesan']) ? trim($data['isiPesan']) : '';

if ($sesiId === '' || !in_array($peran, ['user', 'assistant'], true) || $isiPesan === '') {
    echo json_encode(['success' => false, 'message' => 'Data pesan tidak lengkap.']);
    exit;
}

$cari = $pdo->prepare('SELECT id FROM chat_sesi WHERE sesi_id = ?');
$cari->execute([$sesiId]);
$sesi = $cari->fetch();

if (!$sesi) {
    echo json_encode(['success' => false, 'message' => 'Sesi chat tidak ditemukan.']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO pesan_chat (sesi_id, peran, isi_pesan) VALUES (?, ?, ?)');
$stmt->execute([$sesi['id'], $peran, $isiPesan]);

echo json_encode(['success' => true]);
