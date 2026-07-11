<?php
session_start();
header('Content-Type: application/json');

if (!empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => true,
        'loggedIn' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'nama' => $_SESSION['user_nama'],
            'email' => $_SESSION['user_email'],
            'role' => (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user'),
        ]
    ]);
} else {
    echo json_encode(['success' => true, 'loggedIn' => false]);
}
