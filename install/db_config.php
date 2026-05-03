<?php
$servername = "{{DB_HOST}}";
$username = "{{DB_USERNAME}}";
$password = "{{DB_PASSWORD}}";
$dbname = "{{DB_NAME}}";
$charset = "utf8mb4";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

$conn->set_charset($charset);
