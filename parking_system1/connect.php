<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli("localhost", "root", "", "parking_system1");
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $exception) {
    http_response_code(500);
    exit("Database connection failed. Please check your database configuration.");
}
