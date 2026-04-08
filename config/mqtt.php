<?php

return [
    'broker' => 'broker.hivemq.com',
    'port' => 1883,
    'client_id' => 'parkir-server-' . uniqid(),
    'clean_session' => true,

    'prefix' => 'parking/adhitya',

    // ✅ 2 RFID
    'topic_rfid_entry' => 'rfid/entry',
    'topic_rfid_exit'  => 'rfid/exit',

    // OUTPUT
    'topic_lcd' => 'lcd',
    'topic_entry_servo' => 'entry/servo',
    'topic_exit_servo' => 'exit/servo',
];