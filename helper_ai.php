<?php
function validasiUlasanAI($judul_buku, $teks_review) {
    $apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
    
    // BENAR (Update model ke gemini-3.6-flash)
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=" . $apiKey;

    $prompt = "Kamu adalah validator ulasan buku perpustakaan sekolah.
Tugas: Menilai apakah ulasan siswa LAYAK (VALID) atau TIDAK (INVALID).

Kriteria VALID:
1. Relevan/nyambung dengan buku: '{$judul_buku}'.
2. Bukan spam atau ketikan asal (contoh: 'asdasd', 'bagusss', '12345').
3. Memiliki makna sederhana (kesan, bagian favorit, atau pesan moral).

Catatan: Tolong toleran dengan bahasa santai siswa. Ulasan yang menceritakan alur/kesan buku dianggap VALID.

Teks Ulasan Siswa: \"{$teks_review}\"

Wajib jawab HANYA dalam format JSON:
{
  \"status\": \"VALID\",
  \"alasan\": \"Alasan singkat\"
}";

    $payload = [
        "contents" => [
            ["parts" => [["text" => $prompt]]]
        ],
        "generationConfig" => [
            "response_mime_type" => "application/json",
            "temperature" => 0.2
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
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
        return ['status' => 'INVALID', 'alasan' => 'API Error: ' . $result['error']['message']];
    }

    $rawText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
    
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