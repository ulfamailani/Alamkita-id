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

$nama = trim((isset($d['nama']) ? $d['nama'] : ''));
$lokasi = trim((isset($d['lokasi']) ? $d['lokasi'] : ''));
$provinsi = trim((isset($d['provinsi']) ? $d['provinsi'] : ''));
$kategori_id = (int) ((isset($d['kategori_id']) ? $d['kategori_id'] : 0));
$deskripsi = trim((isset($d['deskripsi']) ? $d['deskripsi'] : ''));

if ($id <= 0 || $nama === '' || $lokasi === '' || $provinsi === '' || $kategori_id <= 0 || $deskripsi === '') {
    echo json_encode(['success' => false, 'message' => 'Nama, lokasi, provinsi, kategori, dan deskripsi wajib diisi.']);
    exit;
}

$lat = ((isset($d['lat']) ? $d['lat'] : '')) !== '' ? (float) $d['lat'] : null;
$lng = ((isset($d['lng']) ? $d['lng'] : '')) !== '' ? (float) $d['lng'] : null;

$stmt = $pdo->prepare("UPDATE destinasi SET
    nama=?, lokasi=?, lat=?, lng=?, provinsi=?, kategori_id=?, deskripsi=?, rating=?, durasi=?, biaya=?,
    biaya_detail=?, rute=?, musim=?, tips=?, keywords=?, emoji=?, foto_url=?, warna_bg=?, warna1=?, warna2=?
    WHERE id=?");

$stmt->execute([
    $nama,
    $lokasi,
    $lat,
    $lng,
    $provinsi,
    $kategori_id,
    $deskripsi,
    (float) ((isset($d['rating']) ? $d['rating'] : 0)),
    (isset($d['durasi']) ? $d['durasi'] : null),
    (isset($d['biaya']) ? $d['biaya'] : null),
    (isset($d['biaya_detail']) ? $d['biaya_detail'] : null),
    (isset($d['rute']) ? $d['rute'] : null),
    (isset($d['musim']) ? $d['musim'] : null),
    (isset($d['tips']) ? $d['tips'] : null),
    (isset($d['keywords']) ? $d['keywords'] : null),
    (isset($d['emoji']) ? $d['emoji'] : null),
    (isset($d['foto_url']) ? $d['foto_url'] : null),
    (isset($d['warna_bg']) ? $d['warna_bg'] : null),
    (isset($d['warna1']) ? $d['warna1'] : null),
    (isset($d['warna2']) ? $d['warna2'] : null),
    $id,
]);

echo json_encode(['success' => true]);