<?php

return [

    'regions' => [
        'mypurecloud.com' => 'Americas (mypurecloud.com)',
        'mypurecloud.ie' => 'Europe (mypurecloud.ie)',
        'mypurecloud.de' => 'Europe (mypurecloud.de)',
        'mypurecloud.com.au' => 'Asia Pacific (mypurecloud.com.au)',
        'usw2.pure.cloud' => 'US West (usw2.pure.cloud)',
        'cac1.pure.cloud' => 'Canada (cac1.pure.cloud)',
    ],

    'bot_connector' => [
        'intents' => ['success', 'escalate', 'failure'],
        'timeout_seconds' => 10,
    ],

    'audiohook' => [
        'path' => '/audiohook',
        'audio_format' => 'PCMU',
        'sample_rate' => 8000,
    ],

];
