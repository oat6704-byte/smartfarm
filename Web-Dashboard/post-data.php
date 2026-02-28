<?php
// --- 1. ตั้งค่าการเชื่อมต่อ ---
include 'config.php';

// กำหนด API Key ให้ตรงกับในโค้ด ESP32
$api_key_value = "lGFFoX4iAliV6L3"; 

// --- 2. รับค่าจาก ESP32 ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- เพิ่มบรรทัดรับค่าและตรวจสอบ API Key ---
    $api_key = $_POST["api_key"];

    if($api_key == $api_key_value) { // ตรวจสอบความถูกต้องของ Key
        
        // รับค่าเซนเซอร์ตาม Architecture ที่กำหนด
        $temp  = $_POST["temp"];
        $hum   = $_POST["hum"];
        $gas   = $_POST["gas"];
        $soil  = $_POST["soil"];
        $relay = $_POST["relay"];

        // --- 3. บันทึกข้อมูลลงฐานข้อมูล (Database Logging) ---
        // แนะนำ: ควรใช้ Prepared Statements เพื่อป้องกัน SQL Injection ในอนาคต
        $sql = "INSERT INTO sensor_data (temperature, humidity, gas_level, soil_moisture, relay_status)
                VALUES ('$temp', '$hum', '$gas', '$soil', '$relay')";

        if ($conn->query($sql) === TRUE) {
            
            // --- 4. ดึงสถานะจากตารางควบคุมเพื่อสั่งการกลับไป (Control Logic) ---
            $result = $conn->query("SELECT status FROM control_panel WHERE id = 1");
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                // ส่ง Payload กลับไปให้ ESP32
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
        echo "Wrong API Key provided.";
    }
} else {
    echo "No POST data received";
}

$conn->close();
?>