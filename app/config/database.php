<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function db(): mysqli {
    static $conn = null;
    if ($conn instanceof mysqli) return $conn;

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = (int)(getenv('DB_PORT') ?: 3306);
    $name = getenv('DB_NAME') ?: 'dinoking_database'; 
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: 'root';

    $conn = new mysqli($host, $user, $pass, $name, $port);
    $conn->set_charset('utf8mb4');
    return $conn;
}