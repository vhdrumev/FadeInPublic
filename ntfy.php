<?php

require_once __DIR__ . '/connection.php';

function sendMessage($text, $contentType = 'text/plain', $headers = [], $timeout = 10) {
    loadEnv(__DIR__ . '/env/ntfy.env');
    $defaultHeaders = [
        'Content-Type: ' . $contentType
    ];
    $headers = array_merge($defaultHeaders, $headers);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => $text,
            'timeout' => $timeout
        ]
    ]);
    return file_get_contents('https://' . $_ENV['USERNAME'] . ':' . $_ENV['PASSWORD'] . '@' . $_ENV['DOMAIN'], false, $context);
}

// Файлът /env/ntfy.env е празен понеже съдържа пароли на публичен сървър
// Идеята е с тази функция да се изпращат нотификации
