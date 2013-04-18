<?php

return [
    'db' => [
        'host' => '',
        'username' => '',
        'password' => '',
        'database' => ''
    ],

    'filePath' => __DIR__ . '/files',

    'timeToCache' => 60 * 60 * 6,

    '404' => [
        'type' => 'image/png',
        'path' => __DIR__ . '/public/media/404.png'
    ],

    'mediaTypes' => [
        "jpg"  => "image/jpeg",
        "gif"  => "image/gif",
        "png"  => "image/png",
        "bmp"  => "image/bmp",
        "ico"  => "image/ico",
        "jpeg" => "image/jpeg",
        "svg"  => "image/svg+xml",
        "ppt"  => "application/vnd.ms-powerpoint",
        "pps"  => "application/vnd.ms-powerpoint",
        "pot"  => "application/vnd.ms-powerpoint",
        "pdf"  => "application/pdf",
        "mp3"  => "audio/mpeg",
        "wav"  => "audio/x-wav",
        "ogg"  => "audio/ogg",
        "mid"  => "audio/midi",
        "avi"  => "video/x-msvideo",
        "wmv"  => "video/x-msvideo",
        "c"    => "text/plain",
        "cpp"  => "text/plain",
        "h"    => "text/plain",
        "hpp"  => "text/plain",
        "doc"  => "application/msword",
        "txt"  => "text/plain",
        "swf"  => "application/x-shockwave-flash",
        "webm" => "video/webm"
    ]
];