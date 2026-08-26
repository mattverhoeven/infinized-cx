<?php

return [

    'product_name' => 'Infinized CX',

    'speech' => [
        'stt_provider' => env('CX_STT_PROVIDER', 'deepgram'),
        'tts_provider' => env('CX_TTS_PROVIDER', 'elevenlabs'),
        'labs_enabled' => env('CX_INFINIZED_LABS_SPEECH', false),
    ],

];
