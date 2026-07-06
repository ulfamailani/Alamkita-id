<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$nama = isset($data['nama']) ? trim($data['nama']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? $data['password'] : '';

if (strlen($nama) < 2) {
    echo json_encode(['success' => false, 'message' => 'Nama minimal 2 karakter.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email tidak valid.']);
    exit;
}
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter.']);
    exit;
}

$cek = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$cek->execute([$email]);
if ($cek->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar, coba login.']);
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, "user")');
$stmt->execute([$nama, $email, $hash]);

$userId = $pdo->lastInsertId();

$_SESSION['user_id'] = $userId;
$_SESSION['user_nama'] = $nama;
$_SESSION['user_email'] = $email;

echo json_encode([
    'success' => true,
    'user' => ['id' => $userId, 'nama' => $nama, 'email' => $email]
]);
