<?php
header('Content-Type: application/json');
require 'db.php';

$sql = "SELECT d.id, d.nama, d.lokasi, d.provinsi, k.nama AS kategori,
               d.deskripsi, d.rating, d.emoji, d.foto_url, d.warna1, d.warna2,
               d.durasi, d.biaya, d.biaya_detail, d.rute, d.musim, d.tips, d.keywords,
               d.lat, d.lng
        FROM destinasi d
        JOIN kategori k ON k.id = d.kategori_id
        ORDER BY d.id";

$rows = $pdo->query($sql)->fetchAll();

$hasil = array_map(function ($r) {
    return [
        'id' => (int) $r['id'],
        'nama' => $r['nama'],
        'lokasi' => $r['lokasi'] . ', ' . $r['provinsi'],
        'tag' => $r['kategori'],
        'desc' => $r['deskripsi'],
        'rating' => (float) $r['rating'],
        'emoji' => $r['emoji'],
        'fotoUrl' => $r['foto_url'] ?: '',
        'warna1' => $r['warna1'] ?: '#16332a',
        'warna2' => $r['warna2'] ?: '#0d2018',
        'durasi' => $r['durasi'] ?: '-',
        'biaya' => $r['biaya'] ?: '-',
        'biayaDetail' => $r['biaya_detail'] ?: 'Belum ada rincian biaya.',
        'rute' => $r['rute'] ?: 'Belum ada info rute.',
        'musim' => $r['musim'] ?: 'Bisa dikunjungi sepanjang tahun.',
        'tips' => $r['tips'] ?: 'Selalu cek info terbaru sebelum berangkat.',
        'keywords' => $r['keywords'] ? explode(',', $r['keywords']) : [strtolower($r['nama'])],
        'lat' => $r['lat'] !== null ? (float) $r['lat'] : null,
        'lng' => $r['lng'] !== null ? (float) $r['lng'] : null,
    ];
}, $rows);

echo json_encode(['success' => true, 'data' => $hasil]);