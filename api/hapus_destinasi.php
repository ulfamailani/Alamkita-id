<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (empty($_SESSION['user_id']) || ((isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user')) !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya admin yang bisa melakukan ini.']);
    exit;
}

$d = json_decode(file_get_contents('php://input'), true);
$id = (int) ((isset($d['id']) ? $d['id'] : 0));

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
    exit;
}

// Bersihkan data terkait dulu biar gak jadi data nyantol/orphan
$pdo->prepare('DELETE FROM destinasi_tag WHERE destinasi_id = ?')->execute([$id]);
$pdo->prepare('DELETE FROM ulasan WHERE destinasi_id = ?')->execute([$id]);
$pdo->prepare('DELETE FROM destinasi WHERE id = ?')->execute([$id]);

echo json_encode(['success' => true]);