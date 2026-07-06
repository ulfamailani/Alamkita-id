<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

function alamkita_random_hex($len){
    if(function_exists('random_bytes')){
        return bin2hex(random_bytes($len));
    } elseif(function_exists('openssl_random_pseudo_bytes')){
        return bin2hex(openssl_random_pseudo_bytes($len));
    }
    return md5(uniqid((string) mt_rand(), true));
}

$sesiId = alamkita_random_hex(16);
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$stmt = $pdo->prepare('INSERT INTO chat_sesi (sesi_id, user_id) VALUES (?, ?)');
$stmt->execute([$sesiId, $userId]);

echo json_encode(['success' => true, 'sesiId' => $sesiId]);
