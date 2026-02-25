<?php
// --- 1. ตั้งค่าการเชื่อมต่อ ---
include 'config.php';

// --- 2. รับค่าจาก ESP32 ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // รับค่าเซนเซอร์ตาม Architecture ที่กำหนด [cite: 39, 41]
    $temp  = $_POST["temp"];
    $hum   = $_POST["hum"];
    $gas   = $_POST["gas"];
    $soil  = $_POST["soil"];
    $relay = $_POST["relay"];

    // --- 3. บันทึกข้อมูลลงฐานข้อมูล (Database Logging)  ---
    $sql = "INSERT INTO sensor_data (temperature, humidity, gas_level, soil_moisture, relay_status)
            VALUES ('$temp', '$hum', '$gas', '$soil', '$relay')";

    if ($conn->query($sql) === TRUE) {
        
        // --- 4. ดึงสถานะจากตารางควบคุมเพื่อสั่งการกลับไป (Control Logic)  ---
        // สมมติว่าตารางชื่อ control_panel มีคอลัมน์ status (1=ON, 0=OFF)
        $result = $conn->query("SELECT status FROM control_panel WHERE id = 1");
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // ส่ง Payload กลับไปให้ ESP32 ตรวจสอบ [cite: 41, 44]
            if ($row['status'] == 1) {
                echo "LED_ON";
            } else {
                echo "LED_OFF";
            }
        } else {
            echo "Data saved, but no control record found.";
        }
        
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "No POST data received";
}

$conn->close();
?>