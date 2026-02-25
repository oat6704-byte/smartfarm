<?php
// ตั้งค่าการเชื่อมต่อฐานข้อมูล MySQL
$servername = "localhost";
$username   = "root";      // ค่าเริ่มต้นของ XAMPP คือ root
$password   = "";          // ค่าเริ่มต้นของ XAMPP คือว่างไว้
$dbname     = "esp32_db";

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตั้งค่าภาษาให้รองรับภาษาไทย
$conn->set_charset("utf8");
?>