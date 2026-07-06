<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Kamu harus masuk dulu untuk menulis ulasan.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$destinasiId = isset($data['destinasiId']) ? (int) $data['destinasiId'] : 0;
$bintang = isset($data['bintang']) ? (int) $data['bintang'] : 0;
$komentar = isset($data['komentar']) ? trim($data['komentar']) : '';

if ($destinasiId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Destinasi tidak valid.']);
    exit;
}
if ($bintang < 1 || $bintang > 5) {
    echo json_encode(['success' => false, 'message' => 'Pilih rating bintang 1-5 dulu.']);
    exit;
}
if (mb_strlen($komentar) < 5) {
    echo json_encode(['success' => false, 'message' => 'Tulis komentar minimal 5 karakter.']);
    exit;
}

$cek = $pdo->prepare('SELECT id FROM destinasi WHERE id = ?');
$cek->execute([$destinasiId]);
if (!$cek->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Destinasi tidak ditemukan.']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO ulasan (destinasi_id, nama_pengunjung, bintang, komentar, user_id)
                        VALUES (?, ?, ?, ?, ?)');
$stmt->execute([
    $destinasiId,
    $_SESSION['user_nama'],
    $bintang,
    $komentar,
    $_SESSION['user_id'],
]);

echo json_encode(['success' => true]);
