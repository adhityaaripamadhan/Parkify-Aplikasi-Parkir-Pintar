<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use PhpMqtt\Client\MqttClient;

$config = require __DIR__ . '/../config/mqtt.php';

// inisialisasi database
$database = new Database();
$conn = $database->connect();

// buat client mqtt - broker, port, client id
$mqtt = new MqttClient(
    $config['broker'],
    $config['port'],
    $config['client_id']
);

$mqtt->connect(); // koneksi ke broker mqtt

// gabungkan prefix dengan topik
$topicEntry = $config['prefix'].'/'.$config['topic_rfid_entry'];
$topicExit  = $config['prefix'].'/'.$config['topic_rfid_exit'];

echo "Listening ENTRY & EXIT...\n";

// entry, kendaraan masuk
$mqtt->subscribe($topicEntry, function ($topic, $message) use ($mqtt, $conn, $config) {

    // ambil id kartu rfid dari device
    $card = trim($message);
    echo "ENTRY RFID: $card\n";

    // cek apakah kartu berstatus IN (sudah masuk)
    $cek = $conn->prepare("
        SELECT id FROM transaksi 
        WHERE card_id = ? AND status = 'IN'
    ");
    $cek->execute([$card]);

    // jika masih berstatus IN (sudah masuk) maka tolak
    if ($cek->fetch()) {
        $mqtt->publish($config['prefix'].'/'.$config['topic_lcd'], 'Sudah Masuk|Tempel Kartu Lain', 0);
        return;
    }

    // insert data kendaraan masuk
    $stmt = $conn->prepare("
        INSERT INTO transaksi (card_id, checkin_time, status)
        VALUES (?, NOW(), 'IN')
    ");
    $stmt->execute([$card]);

    // tampil pesan ke lcd, servo buka palang masuk
    $mqtt->publish($config['prefix'].'/'.$config['topic_lcd'], 'Selamat Datang|Silakan Masuk', 0);
    $mqtt->publish($config['prefix'].'/'.$config['topic_entry_servo'], 'OPEN', 0);

    // balik ke pesan default lcd (delay via mqtt logic esp)
    $mqtt->publish($config['prefix'].'/'.$config['topic_lcd'], 'DEFAULT', 0);

}, 0);


// exit, kendaraan keluar
$mqtt->subscribe($topicExit, function ($topic, $message) use ($mqtt, $conn, $config) {

    // ambil id kartu rfid dari device
    $card = trim($message);
    echo "EXIT RFID: $card\n";

    // cari transaksi terakhir yg masih berstatus IN 
    $cek = $conn->prepare("
        SELECT id, checkin_time FROM transaksi
        WHERE card_id = ? AND status = 'IN'
        ORDER BY id DESC LIMIT 1
    ");
    $cek->execute([$card]);
    $data = $cek->fetch();

    // jika tidak ditemukan tampil pesan eror
    if (!$data) {
        $mqtt->publish($config['prefix'].'/'.$config['topic_lcd'], 'Tidak Ditemukan|Coba Lagi', 0);
        return;
    }

    // update data kendaraan keluar, hitung durasi & biaya
    $stmt = $conn->prepare("
        UPDATE transaksi SET
            checkout_time = NOW(),
            duration = TIMESTAMPDIFF(SECOND, checkin_time, NOW()),
            fee = CEIL(TIMESTAMPDIFF(SECOND, checkin_time, NOW()) / 3600) * 2000,
            status = 'OUT'
        WHERE id = ?
    ");
    $stmt->execute([$data['id']]);

    // ambil total biaya
    $fee = $conn->prepare("SELECT fee FROM transaksi WHERE id=?");
    $fee->execute([$data['id']]);
    $total = $fee->fetchColumn();

    // menampilkan pesan total ke lcd
    $mqtt->publish($config['prefix'].'/'.$config['topic_lcd'], 'Total: Rp'.$total.'|Silakan Bayar', 0);

}, 0);


$mqtt->loop(true);
