<?php
header('Content-Type: application/json');
require 'db.php';

$rows = $pdo->query('SELECT id, nama, emoji FROM kategori ORDER BY id')->fetchAll();

echo json_encode(['success' => true, 'data' => $rows]);