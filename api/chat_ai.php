<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$pesan = isset($data['pesan']) ? trim($data['pesan']) : '';
$riwayat = (isset($data['riwayat']) && is_array($data['riwayat'])) ? $data['riwayat'] : [];

if ($pesan === '') {
    echo json_encode(['success' => false, 'message' => 'Pesan kosong.']);
    exit;
}

/* Ambil data destinasi dari database sebagai konteks jawaban AI (grounding sederhana / RAG) */
$sql = "SELECT d.nama, d.lokasi, d.provinsi, k.nama AS kategori, d.deskripsi,
               d.durasi, d.biaya, d.biaya_detail, d.rute, d.musim, d.tips
        FROM destinasi d
        JOIN kategori k ON k.id = d.kategori_id";
$destinasiList = $pdo->query($sql)->fetchAll();

$konteks = "Data destinasi wisata alam yang tersedia di AlamKita.id:\n";
foreach ($destinasiList as $d) {
    $konteks .= "- {$d['nama']} ({$d['kategori']}, {$d['lokasi']}, {$d['provinsi']}). "
        . "Deskripsi: {$d['deskripsi']} "
        . "Durasi: {$d['durasi']}. Biaya: {$d['biaya']} ({$d['biaya_detail']}). "
        . "Rute: {$d['rute']}. Musim terbaik: {$d['musim']}. Tips: {$d['tips']}\n";
}

$systemPrompt = "Kamu adalah Rama, pemandu wisata AI untuk AlamKita.id, portal wisata alam Indonesia. "
    . "Jawab pertanyaan pengunjung seputar rute, biaya, waktu terbaik, dan tips berkunjung ke destinasi alam "
    . "dengan ramah, singkat (maksimal 4-5 kalimat), dan dalam Bahasa Indonesia. "
    . "Gunakan data destinasi berikut sebagai acuan utama. Jika pertanyaan di luar data ini, "
    . "jawab dengan pengetahuan umum tentang wisata alam Indonesia secara jujur dan tetap membantu:\n\n"
    . $konteks;

$messages = [
    ['role' => 'system', 'content' => $systemPrompt],
];

/* Sertakan riwayat percakapan terakhir supaya AI ingat konteks obrolan */
foreach (array_slice($riwayat, -8) as $r) {
    if (isset($r['role'], $r['content']) && in_array($r['role'], ['user', 'assistant'], true)) {
        $messages[] = ['role' => $r['role'], 'content' => (string) $r['content']];
    }
}
$messages[] = ['role' => 'user', 'content' => $pesan];

$apiKey = getenv('GROQ_API_KEY');
if (!$apiKey) {
    echo json_encode(['success' => false, 'message' => 'GROQ_API_KEY belum diset di server.']);
    exit;
}

$payload = json_encode([
    'model' => 'llama-3.3-70b-versatile',
    'messages' => $messages,
    'temperature' => 0.6,
    'max_tokens' => 350,
]);

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT => 20,
]);
$response = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $httpCode >= 400) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghubungi layanan AI.',
        'debug' => $curlErr ?: $response,
    ]);
    exit;
}

$result = json_decode($response, true);
$balasan = $result['choices'][0]['message']['content'] ?? null;

if (!$balasan) {
    echo json_encode(['success' => false, 'message' => 'AI tidak memberikan balasan yang valid.']);
    exit;
}

echo json_encode(['success' => true, 'balasan' => trim($balasan)]);
