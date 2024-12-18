<?php
// db_connection.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_database";

// 建立連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 設置字符集
$conn->set_charset("utf8mb4");
?>
