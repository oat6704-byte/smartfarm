<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esp32_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// --- ส่วนที่เพิ่ม: จัดการการกดปุ่มสั่งงาน LED ---
if (isset($_GET['led_action'])) {
    $new_status = ($_GET['led_action'] == 'on') ? 1 : 0;
    $conn->query("UPDATE control_panel SET status = $new_status WHERE id = 1");
    header("Location: index.php"); // รีเฟรชเพื่อเคลียร์ค่า URL
    exit();
}

// --- ส่วนที่เพิ่ม: ดึงสถานะ LED ปัจจุบัน ---
$res_led = $conn->query("SELECT status FROM control_panel WHERE id = 1");
$led_data = $res_led->fetch_assoc();
$current_led_status = $led_data['status'] ?? 0;

// ดึงข้อมูลล่าสุด 1 แถวเพื่อโชว์ใน Card
$sql_latest = "SELECT * FROM sensor_data ORDER BY reading_time DESC LIMIT 1";
$result_latest = $conn->query($sql_latest);
$latest = $result_latest->fetch_assoc();

// ดึงข้อมูล 10 รายการล่าสุดเพื่อโชว์ในตาราง
$sql_table = "SELECT * FROM sensor_data ORDER BY reading_time DESC LIMIT 10";
$result_table = $conn->query($sql_table);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP32 IoT Dashboard & Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .status-on { color: #28a745; font-weight: bold; }
        .status-off { color: #dc3545; font-weight: bold; }
        .led-btn { width: 100%; padding: 20px; font-size: 1.2rem; border-radius: 15px; }
    </style>
    <meta http-equiv="refresh" content="30"> 
</head>
<body>

<div class="container py-5">
    <h2 class="text-center mb-4">ESP32 Dashboard & Remote Control</h2>

    <div class="row mb-4 justify-content-center">
        <div class="col-md-6">
            <div class="card p-4 text-center">
                <h4>ควบคุม LED</h4>
                <p>สถานะปัจจุบัน: 
                    <span class="badge <?php echo ($current_led_status == 1) ? 'bg-success' : 'bg-secondary'; ?>">
                        <?php echo ($current_led_status == 1) ? 'เปิดไฟ (ON)' : 'ปิดไฟ (OFF)'; ?>
                    </span>
                </p>
                <div class="row">
                    <div class="col-6">
                        <a href="index.php?led_action=on" class="btn btn-success led-btn <?php echo ($current_led_status == 1) ? 'disabled' : ''; ?>">เปิดไฟ</a>
                    </div>
                    <div class="col-6">
                        <a href="index.php?led_action=off" class="btn btn-danger led-btn <?php echo ($current_led_status == 0) ? 'disabled' : ''; ?>">ปิดไฟ</a>
                    </div>
                </div>
                <small class="text-muted mt-3">หมายเหตุ: ESP32 จะอัปเดตสถานะทุก 30 วินาทีตามรอบการส่งข้อมูล</small>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-5 text-center">
        <div class="col-md-2">
            <div class="card p-3">
                <h5>อุณหภูมิ</h5>
                <h2 class="text-primary"><?php echo $latest['temperature'] ?? '0'; ?> °C</h2>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3">
                <h5>ความชื้นอากาศ</h5>
                <h2 class="text-info"><?php echo $latest['humidity'] ?? '0'; ?> %</h2>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3">
                <h5>ความชื้นดิน</h5>
                <h2 class="text-success"><?php echo $latest['soil_moisture'] ?? '0'; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h5>แก๊ส</h5>
                <h2 class="text-warning"><?php echo $latest['gas_level'] ?? '0'; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h5>ปั๊มน้ำ (Relay)</h5>
                <h2 class="<?php echo ($latest['relay_status'] == 'ON') ? 'status-on' : 'status-off'; ?>">
                    <?php echo $latest['relay_status'] ?? 'N/A'; ?>
                </h2>
            </div>
        </div>
    </div>

    <div class="card p-4">
        <h4 class="mb-3">ประวัติข้อมูลล่าสุด 10 รายการ</h4>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>เวลา</th>
                        <th>อุณหภูมิ (°C)</th>
                        <th>ความชื้น (%)</th>
                        <th>ค่าแก๊ส</th>
                        <th>ความชื้นดิน</th>
                        <th>ปั๊มน้ำ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result_table->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['reading_time']; ?></td>
                        <td><?php echo $row['temperature']; ?></td>
                        <td><?php echo $row['humidity']; ?></td>
                        <td><?php echo $row['gas_level']; ?></td>
                        <td><?php echo $row['soil_moisture']; ?></td>
                        <td>
                            <span class="badge <?php echo ($row['relay_status'] == 'ON') ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $row['relay_status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>