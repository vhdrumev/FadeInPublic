<?php

const BASE_URL = '/fadein/';

function loadEnv($filePath): void {
    if (!file_exists($filePath)) {
        die("Environment file not found.");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

function getDatabaseConnection(): PDO {
    loadEnv(__DIR__ . '/env/config.env');

    $dbHost      = $_ENV['DB_HOST']               ?? 'localhost';
    $dbPort      = $_ENV['DB_PORT']               ?? '3306';
    $dbName      = $_ENV['DB_NAME']               ?? '';
    $dbUser      = $_ENV['DB_USER']               ?? '';
    $dbPass      = $_ENV['DB_PASS']               ?? '';
    $dbCharset   = $_ENV['DB_CHARSET']            ?? 'utf8mb4';
    $dbCollation = $_ENV['DB_COLLATION']          ?? 'utf8mb4_unicode_ci';
    $dbTimeout   = $_ENV['DB_CONNECTION_TIMEOUT'] ?? 10;

    try {
        $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=$dbCharset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $dbCharset COLLATE $dbCollation",
            PDO::ATTR_TIMEOUT            => (int)$dbTimeout,
        ];
        return new PDO($dsn, $dbUser, $dbPass, $options);
    } catch (PDOException $e) {
        $timestamp = date('Y-m-d H:i:s');
        $error_message = "[" . $timestamp . "] Database operation failed: " . $e->getMessage() . "\n";

        $log_dir = __DIR__ . '/log';
        $log_file = $log_dir . '/database.log';
        if (!is_dir($log_dir))
            mkdir($log_dir, 0755, true);
        if (!file_exists($log_file))
            file_put_contents($log_file, '');

        error_log($error_message, 3, $log_file);
        die("A database error occurred. Please try again later.");
    }
}
