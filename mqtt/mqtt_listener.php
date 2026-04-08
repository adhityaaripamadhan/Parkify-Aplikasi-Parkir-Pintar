<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use PhpMqtt\Client\MqttClient;

$config = require __DIR__ . '/../config/mqtt.php';

$database = new Database();
$conn = $database->connect();

$mqtt = new MqttClient(
    $config['broker'],
    $config['port'],
    $config['client_id']
);

$mqtt->connect();

$topicEntry = $config['prefix'].'/'.$config['topic_rfid_entry'];
$topicExit  = $config['prefix'].'/'.$config['topic_rfid_exit'];

echo "Listening ENTRY & EXIT...\n";


// ================= ENTRY =================
$mqtt->subscribe($topicEntry, function ($topic, $message) use ($mqtt, $conn, $config) {

    $card = trim($message);
    echo "ENTRY RFID: $card\n";

    // CEK DOUBLE
    $cek = $conn->prepare("
        SELECT id FROM transaksi 
        WHERE card_id = ? AND status = 'IN'
    ");
    $cek->execute([$card]);

    if ($cek->fetch()) {
        $mqtt->publish($config['prefix'].'/'.$config['topic_lcd'], 'Sudah Masuk|Tempel Kartu Lain', 0);
        return;
    }

    // INSERT
    $stmt = $conn->prepare("
        INSERT INTO transaksi (card_id, checkin_time, status)
        VALUES (?, NOW(), 'IN')
    ");
    $stmt->execute([$card]);

    // LCD + SERVO
    $mqtt->publish($config['prefix'].'/'.$config['topic_lcd'], 'Selamat Datang|Silakan Masuk', 0);
    $mqtt->publish($config['prefix'].'/'.$config['topic_entry_servo'], 'OPEN', 0);

    // ⏱ BALIK KE DEFAULT (delay via MQTT logic ESP)
    $mqtt->publish($config['prefix'].'/'.$config['topic_lcd'], 'DEFAULT', 0);

}, 0);


// ================= EXIT =================
$mqtt->subscribe($topicExit, function ($topic, $message) use ($mqtt, $conn, $config) {

    $card = trim($message);
    echo "EXIT RFID: $card\n";

    $cek = $conn->prepare("
        SELECT id, checkin_time FROM transaksi
        WHERE card_id = ? AND status = 'IN'
        ORDER BY id DESC LIMIT 1
    ");
    $cek->execute([$card]);
    $data = $cek->fetch();

    if (!$data) {
        $mqtt->publish($config['prefix'].'/'.$config['topic_lcd'], 'Tidak Ditemukan|Coba Lagi', 0);
        return;
    }

    // UPDATE
    $stmt = $conn->prepare("
        UPDATE transaksi SET
            checkout_time = NOW(),
            duration = TIMESTAMPDIFF(SECOND, checkin_time, NOW()),
            fee = CEIL(TIMESTAMPDIFF(SECOND, checkin_time, NOW()) / 3600) * 2000,
            status = 'OUT'
        WHERE id = ?
    ");
    $stmt->execute([$data['id']]);

    // AMBIL TOTAL
    $fee = $conn->prepare("SELECT fee FROM transaksi WHERE id=?");
    $fee->execute([$data['id']]);
    $total = $fee->fetchColumn();

    // ❗ TAMPILKAN TOTAL (TIDAK AUTO DEFAULT)
    $mqtt->publish($config['prefix'].'/'.$config['topic_lcd'], 'Total: Rp'.$total.'|Silakan Bayar', 0);

}, 0);


$mqtt->loop(true);