<?php

return [
    'broker' => 'broker.hivemq.com', //alamat broker mqtt
    'port' => 1883, // port standar mqtt
    'client_id' => 'parkir-server-' . uniqid(), // id unik client
    'clean_session' => true,

    'prefix' => 'parking/adhitya', // prefix untuk semua topik

    'topic_rfid_entry' => 'rfid/entry', // topik menerima data rfid saat kendaraan masuk
    'topic_rfid_exit'  => 'rfid/exit', // topik menerima data rfid saat kendaraan keluar

    'topic_lcd' => 'lcd', // topik kirim pesan ke lcd 
    'topic_entry_servo' => 'entry/servo', // topik kontrol servo pintu masuk 
    'topic_exit_servo' => 'exit/servo', // topik kontrol servo pintu masuk 
];
