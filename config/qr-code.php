<?php

use BaconQrCode\Renderer\ImageRendererBackends\GdImageBackEnd;

return [

    'format' => 'png',

    'size' => 100,

    'margin' => 1,

    'error_correction' => 'H',

    'foreground' => [0, 0, 0],

    'background' => [255, 255, 255],

    'style' => null,

    'eye' => null,

    'encoding' => 'UTF-8',

    // 🧠 This is the important line
    'drawingBackend' => GdImageBackEnd::class,

];
