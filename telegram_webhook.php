<?php

// Atur zona waktu ke Asia/Jakarta (GMT+7)
date_default_timezone_set('Asia/Jakarta');

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

// Kelas untuk memformat respons Telegram
class ResponTelegramFormatter {
    private $botToken;
    
    public function __construct($botToken) {
        $this->botToken = $botToken;
    }
    
    // Metode untuk mengirim pesan teks
    public function sendMessage($chat_id, $text, $reply_to_message_id = null) {
        $url = "https://api.telegram.org/bot" . $this->botToken . "/sendMessage";
        $params = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];
        
        if ($reply_to_message_id) {
            $params['reply_to_message_id'] = $reply_to_message_id;
        }
        
        return $this->sendRequest($url, $params);
    }
    
    // Metode untuk mengirim pesan dengan keyboard
    public function sendMessageWithKeyboard($chat_id, $text, $keyboard, $reply_to_message_id = null) {
        $url = "https://api.telegram.org/bot" . $this->botToken . "/sendMessage";
        $params = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ];
        
        if ($reply_to_message_id) {
            $params['reply_to_message_id'] = $reply_to_message_id;
        }
        
        return $this->sendRequest($url, $params);
    }
    
    // Metode untuk mengirim audio
    public function sendAudio($chat_id, $audio_url, $caption = null, $reply_to_message_id = null) {
        $url = "https://api.telegram.org/bot" . $this->botToken . "/sendAudio";
        $params = [
            'chat_id' => $chat_id,
            'audio' => $audio_url
        ];
        
        if ($caption) {
            $params['caption'] = $caption;
            $params['parse_mode'] = 'Markdown';
        }
        
        if ($reply_to_message_id) {
            $params['reply_to_message_id'] = $reply_to_message_id;
        }
        
        return $this->sendRequest($url, $params);
    }
    
    // Metode untuk menjawab callback query
    public function answerCallbackQuery($callback_query_id, $text = null, $show_alert = false) {
        $url = "https://api.telegram.org/bot" . $this->botToken . "/answerCallbackQuery";
        $params = [
            'callback_query_id' => $callback_query_id,
            'show_alert' => $show_alert
        ];
        
        if ($text) {
            $params['text'] = $text;
        }
        
        return $this->sendRequest($url, $params);
    }
    
    // Metode untuk mengirim request ke API Telegram
    private function sendRequest($url, $params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
}
header('content-type: application/json; charset=utf-8');

// Ambil token bot Telegram
$botToken = '<ID-TOKEN>';

// Dapatkan data webhook dari Telegram
$update = json_decode(file_get_contents('php://input'), true);
if (!$update) die('URL ini untuk webhook Telegram.');

// Log data yang masuk
file_put_contents('telegram.txt', '[' . date('Y-m-d H:i:s') . "]\n" . json_encode($update) . "\n\n", FILE_APPEND);

// Inisialisasi objek formatter respons
$responFormatter = new ResponTelegramFormatter($botToken);

// Parsing informasi pesan
$message = isset($update['message']['text']) ? strtolower($update['message']['text']) : '';
$chat_id = isset($update['message']['chat']['id']) ? $update['message']['chat']['id'] : '';
$message_id = isset($update['message']['message_id']) ? $update['message']['message_id'] : '';
$first_name = isset($update['message']['from']['first_name']) ? $update['message']['from']['first_name'] : 'Pengguna';

// Jika tidak ada chat_id, keluar
if (empty($chat_id)) die('Tidak ada chat ID.');

// URL API Al-Quran
$api_url = "https://script.google.com/macros/s/AKfycbyDhS4WMtLO2sSvvKImE6tq4gRcazMPGPkQDzjmIu2xDAeiVnD3mdsRfAetYFvi2RQUjw/exec";

// Variable untuk menyimpan respons
$respon = false;

// Handler untuk perintah /start
if ($message === '/start') {
    $respon = $responFormatter->sendMessage($chat_id, 
        "🕋 *Assalamu'alaikum " . $first_name . "* 🕋\n\n" .
        "Selamat datang di Al-Quran Bot untuk Telegram. Bot ini membantu Anda mengakses Al-Quran langsung dari Telegram.\n\n" .
        "Ketik /help untuk melihat daftar perintah yang tersedia."
    );
}

// Handler untuk perintah /help
if ($message === '/help' || $message === 'quran' || $message === 'alquran') {
    $respon = $responFormatter->sendMessage($chat_id, 
        "🕋 *PANDUAN AL-QURAN BOT* 🕋\n\n" .
        "*DAFTAR PERINTAH:*\n" .
        "1. /surahlist - Menampilkan daftar semua surah\n" .
        "2. /surah {nomor} - Menampilkan info surah dan ayat pertama (contoh: /surah 1)\n" .
        "3. /ayat {surah}:{ayat} - Menampilkan ayat tertentu (contoh: /ayat 1:1)\n" .
        "4. /juz {nomor} - Menampilkan ayat pertama dari juz tertentu\n" .
        "5. /cari {kata kunci} - Mencari ayat berdasarkan kata kunci\n" .
        "6. /audio {surah}:{ayat} - Mendapatkan audio ayat tertentu\n" .
        "7. /tafsir {surah} - Menampilkan tafsir surah\n" .
        "8. /random - Menampilkan ayat acak dari Al-Quran\n" .
        "9. /today - Menampilkan ayat pilihan hari ini\n\n" .
        "Silakan gunakan perintah di atas untuk menjelajahi Al-Quran 🤲"
    );
}

// Menampilkan daftar surah
if ($message === '/surahlist') {
    $url = $api_url . "?action=getAllSurah";
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'success') {
        $message_text = "📖 *DAFTAR SURAH AL-QURAN* 📖\n\n";
        
        // Kelompokkan surah dalam beberapa bagian untuk memudahkan pembacaan
        $total_surah = count($json['data']);
        $surah_per_section = 10;
        $section_count = ceil($total_surah / $surah_per_section);
        
        for ($i = 0; $i < $section_count; $i++) {
            $section_start = ($i * $surah_per_section) + 1;
            $section_end = min((($i + 1) * $surah_per_section), $total_surah);
            
            // Tambahkan surah ke respons teks
            for ($j = ($i * $surah_per_section); $j < min((($i + 1) * $surah_per_section), $total_surah); $j++) {
                $surah = $json['data'][$j];
                $message_text .= $surah['number'] . ". " . $surah['name_id'] . " (" . $surah['name_short'] . ") - " . $surah['translation_id'] . ' - ' . $surah['number_of_verses'] . " ayat\n";
            }
            
            // Tambahkan baris kosong antara setiap kelompok
            if ($i < $section_count - 1) {
                $message_text .= "\n";
            }
        }
        
        $message_text .= "\nKetik /surah {nomor} untuk melihat isi surah.";
        $respon = $responFormatter->sendMessage($chat_id, $message_text);
    } else {
        $respon = $responFormatter->sendMessage($chat_id, "❌ Terjadi kesalahan saat mengambil daftar surah.");
    }
}

// Menampilkan surah berdasarkan nomor
if (preg_match('/^\/surah (\d+)$/', $message, $matches)) {
    $surah_number = $matches[1];
    $url = $api_url . "?action=getSurah&number=" . $surah_number;
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'success' && isset($json['data']['surah'])) {
        $surah = $json['data']['surah'];
        $ayat_pertama = $json['data']['ayat'][0];
        
        $message_text = "📖 *" . $surah['name_id'] . " (" . $surah['name_short'] . ")*\n" .
            $surah['name_long'] . "\n\n" .
            "*Informasi Surah:*\n" .
            "Nomor: " . $surah['number'] . "\n" .
            "Arti: " . $surah['translation_id'] . "\n" .
            "Jumlah Ayat: " . $surah['number_of_verses'] . "\n" .
            "Diturunkan di: " . $surah['revelation_id'] . "\n\n" .
            "*Ayat Pertama:*\n" .
            $ayat_pertama['Arab'] . "\n\n" .
            "Latin: " . $ayat_pertama['Latin'] . "\n" .
            "Arti: " . $ayat_pertama['Text'] . "\n\n" .
            "Ketik /ayat " . $surah_number . ":{nomor ayat} untuk membaca ayat tertentu\n" .
            "Ketik /tafsir " . $surah_number . " untuk membaca tafsir surah\n" .
            "Ketik /audio " . $surah_number . ":1 untuk mendengarkan audio ayat pertama";
            
        $respon = $responFormatter->sendMessage($chat_id, $message_text);
    } else {
        $respon = $responFormatter->sendMessage($chat_id, 
            "❌ Surah dengan nomor " . $surah_number . " tidak ditemukan.\n" .
            "Ketik /surahlist untuk melihat daftar surah yang tersedia."
        );
    }
}

// Menampilkan ayat berdasarkan surah dan nomor ayat
if (preg_match('/^\/ayat (\d+):(\d+)$/', $message, $matches)) {
    $surah_number = $matches[1];
    $ayat_number = $matches[2];
    $url = $api_url . "?action=getAyat&surah=" . $surah_number . "&ayat=" . $ayat_number;
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'success' && isset($json['data']['ayat'])) {
        $surah = $json['data']['surah'];
        $ayat = $json['data']['ayat'];
        
        $message_text = "📖 *" . $surah['name_id'] . " (" . $surah['name_short'] . ") - Ayat " . $ayat_number . "*\n\n" .
            $ayat['Arab'] . "\n\n" .
            $ayat['Latin'] . "\n\n" .
            "*Arti:*\n" .
            $ayat['Text'] . "\n\n" .
            "Halaman: " . $ayat['Page'] . " | Juz: " . $ayat['Juz'] . "\n\n" .
            "Ketik /audio " . $surah_number . ":" . $ayat_number . " untuk mendengarkan audio ayat ini\n" .
            "Ketik /ayat " . $surah_number . ":" . max(1, $ayat_number - 1) . " untuk ayat sebelumnya\n" .
            "Ketik /ayat " . $surah_number . ":" . ($ayat_number + 1) . " untuk ayat berikutnya";
            
        $respon = $responFormatter->sendMessage($chat_id, $message_text);
    } else {
        $respon = $responFormatter->sendMessage($chat_id, 
            "❌ Ayat tidak ditemukan.\n" .
            "Periksa kembali nomor surah dan ayat.\n" .
            "Format: /ayat {nomor_surah}:{nomor_ayat}\n" .
            "Contoh: /ayat 1:1"
        );
    }
}

// Mencari ayat berdasarkan kata kunci
if (preg_match('/^\/cari (.+)$/', $message, $matches)) {
    $keyword = $matches[1];
    $url = $api_url . "?action=search&q=" . urlencode($keyword);
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'success') {
        $total_hasil = $json['count'];
        $message_text = "🔍 *HASIL PENCARIAN: '" . $keyword . "'*\n" .
            "Ditemukan " . $total_hasil . " ayat\n\n";
        
        if ($total_hasil > 0) {
            // Tampilkan 3 hasil pertama
            $max_results = min(3, $total_hasil);
            for ($i = 0; $i < $max_results; $i++) {
                $ayat = $json['data'][$i];
                $surah_name = getSurahName($ayat['Surah']);
                
                $message_text .= "*Q.S. " . $surah_name . " [" . $ayat['Surah'] . ":" . $ayat['Ayah'] . "]*\n" .
                    $ayat['Arab'] . "\n\n" .
                    $ayat['Text'] . "\n\n" .
                    "Ketik /ayat " . $ayat['Surah'] . ":" . $ayat['Ayah'] . " untuk detail lengkap\n\n";
            }
            
            if ($total_hasil > 3) {
                $message_text .= "*... dan " . ($total_hasil - 3) . " ayat lainnya*\n\n" .
                    "_Kata kunci '" . $keyword . "' juga ditemukan dalam ayat-ayat lain. Silakan perjelas pencarian Anda untuk hasil yang lebih spesifik._";
            }
        }
        
        $respon = $responFormatter->sendMessage($chat_id, $message_text);
    } else {
        $respon = $responFormatter->sendMessage($chat_id, 
            "❌ Tidak dapat melakukan pencarian.\n" .
            "Silakan coba dengan kata kunci lain."
        );
    }
}

// Menampilkan juz berdasarkan nomor
if (preg_match('/^\/juz (\d+)$/', $message, $matches)) {
    $juz_number = $matches[1];
    
    if ($juz_number < 1 || $juz_number > 30) {
        $respon = $responFormatter->sendMessage($chat_id, "❌ Nomor juz harus antara 1-30.");
    } else {
        $url = $api_url . "?action=getJuz&number=" . $juz_number;
        $response = file_get_contents($url);
        $json = json_decode($response, true);
        
        if ($json['status'] == 'success') {
            $total_ayat = $json['count'];
            $first_ayat = $json['data'][0];
            $last_ayat = $json['data'][count($json['data']) - 1];
            
            $message_text = "📖 *JUZ " . $juz_number . "*\n" .
                "Total " . $total_ayat . " ayat\n\n" .
                "*Dimulai dari:*\n" .
                "Q.S. " . $first_ayat['Surah'] . ":" . $first_ayat['Ayah'] . " (" . getSurahName($first_ayat['Surah']) . ")\n\n" .
                "*Sampai:*\n" .
                "Q.S. " . $last_ayat['Surah'] . ":" . $last_ayat['Ayah'] . " (" . getSurahName($last_ayat['Surah']) . ")\n\n" .
                "*Ayat Pertama:*\n" .
                $first_ayat['Arab'] . "\n\n" .
                $first_ayat['Text'] . "\n\n" .
                "Ketik /ayat {surah}:{ayat} untuk membaca ayat tertentu";
                
            $respon = $responFormatter->sendMessage($chat_id, $message_text);
        } else {
            $respon = $responFormatter->sendMessage($chat_id, "❌ Terjadi kesalahan saat mengambil data juz.");
        }
    }
}

// Mendapatkan audio ayat
if (preg_match('/^\/audio (\d+):(\d+)$/', $message, $matches)) {
    $surah_number = $matches[1];
    $ayat_number = $matches[2];
    
    // Dapatkan info surah dulu
    $url_surah = $api_url . "?action=getSurah&number=" . $surah_number;
    $response_surah = file_get_contents($url_surah);
    $json_surah = json_decode($response_surah, true);
    
    if ($json_surah['status'] == 'success' && isset($json_surah['data']['surah'])) {
        $surah_info = $json_surah['data']['surah'];
        
        // Kemudian dapatkan audio ayat
        $url = $api_url . "?action=getAudio&surah=" . $surah_number . "&ayat=" . $ayat_number . "&qari=default";
        $response = file_get_contents($url);
        $json = json_decode($response, true);
        
        if ($json['status'] == 'success' && isset($json['audio_url'])) {
            $ayat_info = $json['ayat_info'];
            $audio_url = $json['audio_url'];
            
            $caption = "🎧 *AUDIO AL-QURAN*\n\n" .
                       "📖 *Surah " . $surah_info['name_id'] . " (" . $surah_info['name_short'] . ")*\n" .
                       "Ayat " . $ayat_number . " dari " . $surah_info['number_of_verses'] . " ayat\n\n" .
                       $ayat_info['Arab'] . "\n\n" .
                       "*Latin:* " . $ayat_info['Latin'] . "\n\n" .
                       "*Arti:* " . $ayat_info['Text'] . "\n\n" .
                       "Ketik /ayat " . $surah_number . ":" . ($ayat_number + 1) . " untuk ayat berikutnya";
            
            // Kirim audio dengan caption
            $respon = $responFormatter->sendAudio($chat_id, $audio_url, $caption);
        } else {
            $respon = $responFormatter->sendMessage($chat_id, 
                "❌ Audio tidak ditemukan.\n" .
                "Periksa kembali nomor surah dan ayat."
            );
        }
    } else {
        $respon = $responFormatter->sendMessage($chat_id, "❌ Surah dengan nomor " . $surah_number . " tidak ditemukan.");
    }
}

// Menampilkan tafsir surah
if (preg_match('/^\/tafsir (\d+)$/', $message, $matches)) {
    $surah_number = $matches[1];
    $url = $api_url . "?action=getSurah&number=" . $surah_number;
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'success' && isset($json['data']['surah'])) {
        $surah = $json['data']['surah'];
        
        $message_text = "📚 *TAFSIR SURAH " . $surah['name_id'] . "*\n" .
            $surah['name_long'] . "\n\n" .
            $surah['tafsir'] . "\n\n" .
            "Ketik /surah " . $surah_number . " untuk membaca ayat-ayat surah ini";
            
        $respon = $responFormatter->sendMessage($chat_id, $message_text);
    } else {
        $respon = $responFormatter->sendMessage($chat_id, "❌ Surah dengan nomor " . $surah_number . " tidak ditemukan.");
    }
}

// Fitur tambahan: Ayat Random
if ($message === '/random') {
    // Generate random surah and ayat
    $random_surah = rand(1, 114);
    
    // Get info about the surah to know max ayat
    $url = $api_url . "?action=getSurah&number=" . $random_surah;
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'success' && isset($json['data']['surah'])) {
        $surah = $json['data']['surah'];
        $max_ayat = $surah['number_of_verses'];
        $random_ayat = rand(1, $max_ayat);
        
        // Get the random ayat
        $url = $api_url . "?action=getAyat&surah=" . $random_surah . "&ayat=" . $random_ayat;
        $response = file_get_contents($url);
        $json = json_decode($response, true);
        
        if ($json['status'] == 'success' && isset($json['data']['ayat'])) {
            $ayat = $json['data']['ayat'];
            
            $message_text = "📖 *AYAT ACAK*\n\n" .
                "*Q.S. " . $surah['name_id'] . " [" . $random_surah . ":" . $random_ayat . "]*\n\n" .
                $ayat['Arab'] . "\n\n" .
                $ayat['Latin'] . "\n\n" .
                "*Arti:*\n" .
                $ayat['Text'] . "\n\n" .
                "Ketik /ayat " . $random_surah . ":" . $random_ayat . " untuk melihat detail ayat ini\n" .
                "Ketik /audio " . $random_surah . ":" . $random_ayat . " untuk mendengarkan ayat ini";
                
            $respon = $responFormatter->sendMessage($chat_id, $message_text);
        }
    }
    
    if (!$respon) {
        $respon = $responFormatter->sendMessage($chat_id, 
            "❌ Terjadi kesalahan saat mengambil ayat acak.\n" .
            "Silakan coba lagi."
        );
    }
}

// Fitur tambahan: Ayat Hari Ini
if ($message === '/today') {
    // Menggunakan tanggal hari ini sebagai seed untuk mendapatkan ayat yang konsisten per hari
    $today = date('Y-m-d');
    $seed = crc32($today);
    srand($seed);
    
    $surah_number = rand(1, 114);
    
    // Get info about the surah to know max ayat
    $url = $api_url . "?action=getSurah&number=" . $surah_number;
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'success' && isset($json['data']['surah'])) {
        $surah = $json['data']['surah'];
        $max_ayat = $surah['number_of_verses'];
        $ayat_number = rand(1, $max_ayat);
        
        // Get the ayat
        $url = $api_url . "?action=getAyat&surah=" . $surah_number . "&ayat=" . $ayat_number;
        $response = file_get_contents($url);
        $json = json_decode($response, true);
        
        if ($json['status'] == 'success' && isset($json['data']['ayat'])) {
            $ayat = $json['data']['ayat'];
            
            $message_text = "📅 *AYAT PILIHAN HARI INI (" . date('d-m-Y') . ")*\n\n" .
                "*Q.S. " . $surah['name_id'] . " [" . $surah_number . ":" . $ayat_number . "]*\n\n" .
                $ayat['Arab'] . "\n\n" .
                $ayat['Text'] . "\n\n" .
                "_\"Semoga ayat ini membawa manfaat dan berkah untuk hari Anda\"_\n\n" .
                "Ketik /tafsir " . $surah_number . " untuk membaca tafsir surah ini";
                
            $respon = $responFormatter->sendMessage($chat_id, $message_text);
        }
    }
    
    if (!$respon) {
        $respon = $responFormatter->sendMessage($chat_id, 
            "❌ Terjadi kesalahan saat mengambil ayat pilihan hari ini.\n" .
            "Silakan coba lagi."
        );
    }
    
    // Reset random seed
    srand();
}

// Fitur tambahan: Jadwal Sholat
if (preg_match('/^\/sholat (.+)$/', $message, $matches)) {
    $kota = $matches[1];
    // Ini hanya simulasi, dalam implementasi nyata Anda perlu menggunakan API jadwal sholat
    // seperti https://api.myquran.com/ atau https://api.pray.zone/
    
    // Simulasi data jadwal sholat
    $waktu_sholat = [
        'subuh' => date('H:i', strtotime('04:' . rand(10, 59))),
        'dzuhur' => date('H:i', strtotime('12:' . rand(0, 15))),
        'ashar' => date('H:i', strtotime('15:' . rand(10, 30))),
        'maghrib' => date('H:i', strtotime('18:' . rand(0, 15))),
        'isya' => date('H:i', strtotime('19:' . rand(15, 30)))
    ];
    
    $message_text = "🕌 *JADWAL SHOLAT*\n" .
        "Kota: " . ucwords($kota) . "\n" .
        "Tanggal: " . date('d-m-Y') . "\n\n" .
        "Subuh: " . $waktu_sholat['subuh'] . "\n" .
        "Dzuhur: " . $waktu_sholat['dzuhur'] . "\n" .
        "Ashar: " . $waktu_sholat['ashar'] . "\n" .
        "Maghrib: " . $waktu_sholat['maghrib'] . "\n" .
        "Isya: " . $waktu_sholat['isya'] . "\n\n" .
        "_*Jadwal ini hanya perkiraan. Silakan periksa jadwal resmi setempat._\n\n" .
        "Ketik /today untuk mendapatkan ayat pilihan hari ini";
        
    $respon = $responFormatter->sendMessage($chat_id, $message_text);
}

// Handle perintah /menu - Menampilkan menu dengan tombol inline (inline keyboard)
if ($message === '/menu') {
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '📖 Daftar Surah', 'callback_data' => 'surahlist'],
                ['text' => '🔍 Cari Ayat', 'callback_data' => 'search']
            ],
            [
                ['text' => '🎲 Ayat Random', 'callback_data' => 'random'],
                ['text' => '📅 Ayat Hari Ini', 'callback_data' => 'today']
            ],
            [
                ['text' => '🕌 Jadwal Sholat', 'callback_data' => 'sholat'],
                ['text' => '❓ Bantuan', 'callback_data' => 'help']
            ]
        ]
    ];
    
    $respon = $responFormatter->sendMessageWithKeyboard($chat_id, 
        "🕋 *MENU AL-QURAN BOT* 🕋\n\n" .
        "Silakan pilih menu di bawah ini:",
        $keyboard
    );
}

// Handle callback query (untuk tombol inline)
if (isset($update['callback_query'])) {
    $callback_query_id = $update['callback_query']['id'];
    $callback_data = $update['callback_query']['data'];
    $callback_chat_id = $update['callback_query']['message']['chat']['id'];
    
    // Acknowledge callback query
    $responFormatter->answerCallbackQuery($callback_query_id);
    
    // Handle berbagai callback data
    switch ($callback_data) {
        case 'surahlist':
            // Redirect ke perintah /surahlist
            $url = $api_url . "?action=getAllSurah";
            $response = file_get_contents($url);
            $json = json_decode($response, true);
            
            if ($json['status'] == 'success') {
                $message_text = "📖 *DAFTAR SURAH AL-QURAN* 📖\n\n";
                
                // Kelompokkan surah dalam beberapa bagian untuk memudahkan pembacaan
                $total_surah = count($json['data']);
                $surah_per_section = 10;
                $section_count = ceil($total_surah / $surah_per_section);
                
                for ($i = 0; $i < $section_count; $i++) {
                    $section_start = ($i * $surah_per_section) + 1;
                    $section_end = min((($i + 1) * $surah_per_section), $total_surah);
                    
                    // Tambahkan surah ke respons teks
                    for ($j = ($i * $surah_per_section); $j < min((($i + 1) * $surah_per_section), $total_surah); $j++) {
                        $surah = $json['data'][$j];
                        $message_text .= $surah['number'] . ". " . $surah['name_id'] . " (" . $surah['name_short'] . ") - " . $surah['number_of_verses'] . " ayat\n";
                    }
                    
                    // Tambahkan baris kosong antara setiap kelompok
                    if ($i < $section_count - 1) {
                        $message_text .= "\n";
                    }
                }
                
                $message_text .= "\nKetik /surah {nomor} untuk melihat isi surah.";
                $respon = $responFormatter->sendMessage($callback_chat_id, $message_text);
            } else {
                $respon = $responFormatter->sendMessage($callback_chat_id, "❌ Terjadi kesalahan saat mengambil daftar surah.");
            }
            break;
        case 'random':
            // Generate random surah and ayat
            $random_surah = rand(1, 114);
            
            // Get info about the surah to know max ayat
            $url = $api_url . "?action=getSurah&number=" . $random_surah;
            $response = file_get_contents($url);
            $json = json_decode($response, true);
            
            if ($json['status'] == 'success' && isset($json['data']['surah'])) {
                $surah = $json['data']['surah'];
                $max_ayat = $surah['number_of_verses'];
                $random_ayat = rand(1, $max_ayat);
                
                // Get the random ayat
                $url = $api_url . "?action=getAyat&surah=" . $random_surah . "&ayat=" . $random_ayat;
                $response = file_get_contents($url);
                $json = json_decode($response, true);
                
                if ($json['status'] == 'success' && isset($json['data']['ayat'])) {
                    $ayat = $json['data']['ayat'];
                    
                    $message_text = "📖 *AYAT ACAK*\n\n" .
                        "*Q.S. " . $surah['name_id'] . " [" . $random_surah . ":" . $random_ayat . "]*\n\n" .
                        $ayat['Arab'] . "\n\n" .
                        $ayat['Latin'] . "\n\n" .
                        "*Arti:*\n" .
                        $ayat['Text'] . "\n\n" .
                        "Ketik /ayat " . $random_surah . ":" . $random_ayat . " untuk melihat detail ayat ini\n" .
                        "Ketik /audio " . $random_surah . ":" . $random_ayat . " untuk mendengarkan ayat ini";
                        
                    $respon = $responFormatter->sendMessage($callback_chat_id, $message_text);
                }
            }
            
            if (!$respon) {
                $respon = $responFormatter->sendMessage($callback_chat_id, 
                    "❌ Terjadi kesalahan saat mengambil ayat acak.\n" .
                    "Silakan coba lagi."
                );
            }
            break;
        case 'today':
            // Menggunakan tanggal hari ini sebagai seed untuk mendapatkan ayat yang konsisten per hari
            $today = date('Y-m-d');
            $seed = crc32($today);
            srand($seed);
            
            $surah_number = rand(1, 114);
            
            // Get info about the surah to know max ayat
            $url = $api_url . "?action=getSurah&number=" . $surah_number;
            $response = file_get_contents($url);
            $json = json_decode($response, true);
            
            if ($json['status'] == 'success' && isset($json['data']['surah'])) {
                $surah = $json['data']['surah'];
                $max_ayat = $surah['number_of_verses'];
                $ayat_number = rand(1, $max_ayat);
                
                // Get the ayat
                $url = $api_url . "?action=getAyat&surah=" . $surah_number . "&ayat=" . $ayat_number;
                $response = file_get_contents($url);
                $json = json_decode($response, true);
                
                if ($json['status'] == 'success' && isset($json['data']['ayat'])) {
                    $ayat = $json['data']['ayat'];
                    
                    $message_text = "📅 *AYAT PILIHAN HARI INI (" . date('d-m-Y') . ")*\n\n" .
                        "*Q.S. " . $surah['name_id'] . " [" . $surah_number . ":" . $ayat_number . "]*\n\n" .
                        $ayat['Arab'] . "\n\n" .
                        $ayat['Text'] . "\n\n" .
                        "_\"Semoga ayat ini membawa manfaat dan berkah untuk hari Anda\"_\n\n" .
                        "Ketik /tafsir " . $surah_number . " untuk membaca tafsir surah ini";
                        
                    $respon = $responFormatter->sendMessage($callback_chat_id, $message_text);
                }
            }
            
            if (!$respon) {
                $respon = $responFormatter->sendMessage($callback_chat_id, 
                    "❌ Terjadi kesalahan saat mengambil ayat pilihan hari ini.\n" .
                    "Silakan coba lagi."
                );
            }
            
            // Reset random seed
            srand();
            break;
        case 'help':
            $respon = $responFormatter->sendMessage($callback_chat_id, 
                "🕋 *PANDUAN AL-QURAN BOT* 🕋\n\n" .
                "*DAFTAR PERINTAH:*\n" .
                "1. /surahlist - Menampilkan daftar semua surah\n" .
                "2. /surah {nomor} - Menampilkan info surah dan ayat pertama (contoh: /surah 1)\n" .
                "3. /ayat {surah}:{ayat} - Menampilkan ayat tertentu (contoh: /ayat 1:1)\n" .
                "4. /juz {nomor} - Menampilkan ayat pertama dari juz tertentu\n" .
                "5. /cari {kata kunci} - Mencari ayat berdasarkan kata kunci\n" .
                "6. /audio {surah}:{ayat} - Mendapatkan audio ayat tertentu\n" .
                "7. /tafsir {surah} - Menampilkan tafsir surah\n" .
                "8. /random - Menampilkan ayat acak dari Al-Quran\n" .
                "9. /today - Menampilkan ayat pilihan hari ini\n\n" .
                "Silakan gunakan perintah di atas untuk menjelajahi Al-Quran 🤲"
            );
            break;
        case 'search':
            // Kirim pesan untuk meminta kata kunci pencarian
            $respon = $responFormatter->sendMessage($callback_chat_id, 
                "🔍 *PENCARIAN AYAT*\n\n" .
                "Silakan ketik perintah berikut untuk mencari ayat:\n" .
                "/cari {kata kunci}\n\n" .
                "Contoh: /cari rahmat"
            );
            break;
        case 'sholat':
            // Kirim pesan untuk meminta nama kota
            $respon = $responFormatter->sendMessage($callback_chat_id, 
                "🕌 *JADWAL SHOLAT*\n\n" .
                "Silakan ketik perintah berikut untuk melihat jadwal sholat:\n" .
                "/sholat {nama kota}\n\n" .
                "Contoh: /sholat jakarta"
            );
            break;
    }
}

// Fungsi bantuan untuk mendapatkan nama surah
function getSurahName($surah_number) {
    // Ideally, we'd cache this data to avoid repeated API calls
    $surah_names = [
        1 => "Al-Fatihah", 2 => "Al-Baqarah", 3 => "Ali 'Imran", 4 => "An-Nisa'",
        5 => "Al-Ma'idah", 6 => "Al-An'am", 7 => "Al-A'raf", 8 => "Al-Anfal",
        9 => "At-Taubah", 10 => "Yunus", 11 => "Hud", 12 => "Yusuf",
        13 => "Ar-Ra'd", 14 => "Ibrahim", 15 => "Al-Hijr", 16 => "An-Nahl",
        17 => "Al-Isra'", 18 => "Al-Kahf", 19 => "Maryam", 20 => "Ta Ha",
        21 => "Al-Anbiya'", 22 => "Al-Hajj", 23 => "Al-Mu'minun", 24 => "An-Nur",
        25 => "Al-Furqan", 26 => "Asy-Syu'ara'", 27 => "An-Naml", 28 => "Al-Qasas",
        29 => "Al-'Ankabut", 30 => "Ar-Rum", 31 => "Luqman", 32 => "As-Sajdah",
        33 => "Al-Ahzab", 34 => "Saba'", 35 => "Fatir", 36 => "Ya Sin",
        37 => "As-Saffat", 38 => "Sad", 39 => "Az-Zumar", 40 => "Gafir",
        41 => "Fussilat", 42 => "Asy-Syura", 43 => "Az-Zukhruf", 44 => "Ad-Dukhan",
        45 => "Al-Jasiyah", 46 => "Al-Ahqaf", 47 => "Muhammad", 48 => "Al-Fath",
        49 => "Al-Hujurat", 50 => "Qaf", 51 => "Az-Zariyat", 52 => "At-Tur",
        53 => "An-Najm", 54 => "Al-Qamar", 55 => "Ar-Rahman", 56 => "Al-Waqi'ah",
        57 => "Al-Hadid", 58 => "Al-Mujadilah", 59 => "Al-Hasyr", 60 => "Al-Mumtahanah",
        61 => "As-Saff", 62 => "Al-Jumu'ah", 63 => "Al-Munafiqun", 64 => "At-Tagabun",
        65 => "At-Talaq", 66 => "At-Tahrim", 67 => "Al-Mulk", 68 => "Al-Qalam",
        69 => "Al-Haqqah", 70 => "Al-Ma'arij", 71 => "Nuh", 72 => "Al-Jinn",
        73 => "Al-Muzzammil", 74 => "Al-Muddassir", 75 => "Al-Qiyamah", 76 => "Al-Insan",
        77 => "Al-Mursalat", 78 => "An-Naba'", 79 => "An-Nazi'at", 80 => "'Abasa",
        81 => "At-Takwir", 82 => "Al-Infitar", 83 => "Al-Mutaffifin", 84 => "Al-Insyiqaq",
        85 => "Al-Buruj", 86 => "At-Tariq", 87 => "Al-A'la", 88 => "Al-Gasyiyah",
        89 => "Al-Fajr", 90 => "Al-Balad", 91 => "Asy-Syams", 92 => "Al-Lail",
        93 => "Ad-Duha", 94 => "Asy-Syarh", 95 => "At-Tin", 96 => "Al-'Alaq",
        97 => "Al-Qadr", 98 => "Al-Bayyinah", 99 => "Az-Zalzalah", 100 => "Al-'Adiyat",
        101 => "Al-Qari'ah", 102 => "At-Takasur", 103 => "Al-'Asr", 104 => "Al-Humazah",
        105 => "Al-Fil", 106 => "Quraisy", 107 => "Al-Ma'un", 108 => "Al-Kausar",
        109 => "Al-Kafirun", 110 => "An-Nasr", 111 => "Al-Lahab", 112 => "Al-Ikhlas",
        113 => "Al-Falaq", 114 => "An-Nas"
		];
    
    return isset($surah_names[$surah_number]) ? $surah_names[$surah_number] : "Surah " . $surah_number;
}

// Kirim data debugging ke file log
if ($respon) {
    file_put_contents('telegram_respon.txt', '[' . date('Y-m-d H:i:s') . "]\n" . $respon . "\n\n", FILE_APPEND);
}

// Set webhook (untuk keperluan setup awal)
if (isset($_GET['setwebhook'])) {
    $webhook_url = isset($_GET['url']) ? $_GET['url'] : "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $setWebhook = "https://api.telegram.org/bot" . $botToken . "/setWebhook?url=" . urlencode($webhook_url);
    echo "Setting webhook to: " . $webhook_url . "<br>";
    $result = file_get_contents($setWebhook);
    echo "Response: " . $result;
    die();
}

// Delete webhook (untuk keperluan reset)
if (isset($_GET['deletewebhook'])) {
    $deleteWebhook = "https://api.telegram.org/bot" . $botToken . "/deleteWebhook";
    echo "Deleting webhook...<br>";
    $result = file_get_contents($deleteWebhook);
    echo "Response: " . $result;
    die();
}

// Get webhook info (untuk keperluan debugging)
if (isset($_GET['webhookinfo'])) {
    $webhookInfo = "https://api.telegram.org/bot" . $botToken . "/getWebhookInfo";
    echo "Getting webhook info...<br>";
    $result = file_get_contents($webhookInfo);
    echo "Response: " . $result;
    die();
}

// Output respons untuk debug jika diakses langsung
if (empty($update)) {
    echo '
    <html>
    <head><title>Telegram Al-Quran Bot</title></head>
    <body>
        <h1>Telegram Al-Quran Bot Webhook</h1>
        <p>Webhook ini digunakan untuk bot Telegram Al-Quran.</p>
        <p>Gunakan parameter berikut untuk keperluan debugging:</p>
        <ul>
            <li><a href="?setwebhook">setwebhook</a> - Set webhook ke URL saat ini</li>
            <li><a href="?setwebhook&url=https://example.com/webhook.php">setwebhook&url=...</a> - Set webhook ke URL tertentu</li>
            <li><a href="?deletewebhook">deletewebhook</a> - Hapus webhook</li>
            <li><a href="?webhookinfo">webhookinfo</a> - Tampilkan informasi webhook</li>
        </ul>
    </body>
    </html>';
    die();
}
?>
