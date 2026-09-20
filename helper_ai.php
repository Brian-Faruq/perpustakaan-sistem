<?php
// Fungsi sederhana untuk membaca file .env
function loadEnv($filePath = __DIR__ . '/.env') {
    if (!file_exists($filePath)) return;
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Panggil pembaca .env
loadEnv();

function validasiUlasanAI($judul_buku, $penulis_buku, $sinopsis_buku, $teks_review) {
    $apiKey = $_ENV['TAMANDATA_API_KEY'] ?? '9r_live_YEGu31DqzVFMUnHX7yLae9GfYoJ9Ixoj';
    
    $url = "https://ai.tamandata.com/v1/chat/completions";

    // Potong sinopsis jika terlalu panjang
    $sinopsis_singkat = !empty($sinopsis_buku) ? mb_strimwidth($sinopsis_buku, 0, 800, "...") : "Tidak ada sinopsis.";

    // Prompt dengan pemeriksaan kesesuaian judul & konteks buku
    $prompt = "Kamu adalah validator ulasan buku perpustakaan sekolah.
Tugas utama: Menilai apakah ulasan yang ditulis siswa BENAR-BENAR COCOK dan RELEVAN dengan buku yang dipilih.

---
BUKU YANG DIPILIH SISWA:
Judul Buku : {$judul_buku}
Penulis    : {$penulis_buku}
Sinopsis   : \"{$sinopsis_singkat}\"
---

TEKS ULASAN SISWA:
\"{$teks_review}\"

ATURAN VALIDASI:
1. Kategori VALID:
   - Ulasan cocok atau relevan dengan judul '{$judul_buku}', penulis, atau sinopsisnya.
   - Ulasan berisi pendapat umum/kesan membaca yang wajar sesuai tema buku tersebut.

2. Kategori INVALID:
   - SALAH BUKU: Siswa memilih buku '{$judul_buku}', TETAPI isi ulasannya secara jelas membahas BUKU LAIN/CERITA LAIN (contoh: Pilih 'Bumi' tapi bahas Harry Potter, Laskar Pelangi, Naruto, atau judul lain yang tidak ada hubungannya).
   - SPAM / ASAL KETIK: Berupa ketikan asal (contoh: 'asdasd', '12345', 'bagusss bangetttt bgt').
   - BAHASA TIDAK DESAKRAL/KASAR: Menggunakan kata-kata kotor atau promosi.

Wajib jawab HANYA dalam format JSON valid (tanpa markdown / backtick):
{
  \"status\": \"VALID\",
  \"alasan\": \"Alasan singkat penentuan valid/invalid\"
}";

    $payload = [
        "model" => "tamandata",
        "messages" => [
            [
                "role" => "user",
                "content" => $prompt
            ]
        ],
        "temperature" => 0.2
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return ['status' => 'INVALID', 'alasan' => 'cURL Error: ' . $error_msg];
    }
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['error'])) {
        return ['status' => 'INVALID', 'alasan' => 'API Error: ' . ($result['error']['message'] ?? 'Unknown Error')];
    }

    $rawText = $result['choices'][0]['message']['content'] ?? '';
    
    if (preg_match('/\{.*\}/s', $rawText, $matches)) {
        $parsedData = json_decode($matches[0], true);
        if (is_array($parsedData) && isset($parsedData['status'])) {
            return $parsedData;
        }
    }

    return [
        'status' => 'INVALID', 
        'alasan' => 'Respons AI tidak valid.'
    ];
}