<?php
$conn = new mysqli('localhost', 'root', '', 'shop');
$conn->set_charset('utf8mb4');
if ($conn->connect_error) {
die('Kết nối thất bại');
}