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

function validasiUlasanAI($judul_buku, $teks_review) {
    $apiKey = $_ENV['TAMANDATA_API_KEY'] ?? '9r_live_YEGu31DqzVFMUnHX7yLae9GfYoJ9Ixoj';
    
    $url = "https://ai.tamandata.com/v1/chat/completions";

    $prompt = "Kamu adalah validator ulasan buku perpustakaan sekolah.
Tugas: Menilai apakah ulasan siswa LAYAK (VALID) atau TIDAK (INVALID).

Kriteria VALID:
1. Relevan/nyambung dengan buku: '{$judul_buku}'.
2. Bukan spam atau ketikan asal (contoh: 'asdasd', 'bagusss', '12345').
3. Memiliki makna sederhana (kesan, bagian favorit, atau pesan moral).

Catatan: Tolong toleran dengan bahasa santai siswa. Ulasan yang menceritakan alur/kesan buku dianggap VALID.

Teks Ulasan Siswa: \"{$teks_review}\"

Wajib jawab HANYA dalam format JSON valid:
{
  \"status\": \"VALID\",
  \"alasan\": \"Alasan singkat\"
}";

    // 3. Payload sesuai spesifikasi Tamandata / OpenAI Format
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

    // 4. Parsing response dari Tamandata
    $rawText = $result['choices'][0]['message']['content'] ?? '';
    
    if (preg_match('/\{.*\}/s', $rawText, $matches)) {
        $parsedData = json_decode($matches[0], true);
        if (is_array($parsedData) && isset($parsedData['status'])) {
            return $parsedData;
        }
    }

    return [
        'status' => 'INVALID', 
        'alasan' => 'Respons AI tidak valid. Teks mentah: ' . substr($rawText, 0, 80)
    ];
}