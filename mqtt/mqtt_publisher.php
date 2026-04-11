<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpMqtt\Client\MqttClient;

$config = require __DIR__ . '/../config/mqtt.php';

// buat client mqtt - broker, port, client id
$mqtt = new MqttClient(
    $config['broker'],
    $config['port'],
    'publisher-' . uniqid()
);

// koneksi ke broker
$mqtt->connect(null, true);

// tes lcd format bari1|baris2
$mqtt->publish(
    $config['prefix'] . '/' . $config['topic_lcd'],
    'TEST LCD|OK',
    0
);

// tes servo entry (palang masuk)
$mqtt->publish(
    $config['prefix'] . '/' . $config['topic_entry_servo'],
    'OPEN',
    0
);

// tes servo exit (palang keluar)
$mqtt->publish(
    $config['prefix'] . '/' . $config['topic_exit_servo'],
    'OPEN',
    0
);

// diskonek broker setelah tes selesai
$mqtt->disconnect();

echo "MQTT TEST SUCCESS\n";
