<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? $data['password'] : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Email atau password tidak valid.']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, nama, email, password, role FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Email atau password salah.']);
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_nama'] = $user['nama'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = (isset($user['role']) ? $user['role'] : 'user');

echo json_encode([
    'success' => true,
    'user' => ['id' => $user['id'], 'nama' => $user['nama'], 'email' => $user['email'], 'role' => $_SESSION['user_role']]
]);
