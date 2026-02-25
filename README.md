🌿 Smart Farm IoT: Edge Computing & Monitoring Systemระบบฟาร์มอัจฉริยะที่ผสมผสานการทำงานระหว่างอุปกรณ์ IoT และระบบบริหารจัดการผ่านเว็บ โดยมีจุดเด่นด้านการประมวลผลที่อุปกรณ์ (Edge Computing) เพื่อความรวดเร็วและแม่นยำ 📌 ภาพรวมของระบบ (System Overview)โครงงานนี้พัฒนาขึ้นเพื่อจัดการสภาพแวดล้อมในโรงเรือนจำลอง โดยมีระบบการทำงานหลักดังนี้:Automation: ควบคุมปั๊มน้ำอัตโนมัติจากค่าความชื้นดิน และแจ้งเตือนแก๊สรั่วผ่านเสียง Real-time Monitoring: แสดงค่าอุณหภูมิ, ความชื้นอากาศ, ความชื้นดิน และระดับแก๊ส ผ่าน Dashboard Remote Control: สั่งงานเปิด-ปิดไฟจากระยะไกลผ่านหน้าเว็บไซต์ Cloud Notification: แจ้งเตือนสถานะวิกฤตผ่าน Telegram Bot 🏗️ สถาปัตยกรรมของระบบ (System Architecture)Plaintext          +-------------+
          |    ESP32    | <--- (Sensor: DHT22, Soil, MQ135)
          | (Edge Logic)| ---> (Actuator: Relay, Buzzer, LED)
          +------+------+ 
                 |
                 | HTTP POST (with API Key Security)
                 v
        +------------------+
        |   PHP Server     | <--- (post-data.php)
        |    (XAMPP)       | <--- (index.php Dashboard)
        +--------+---------+
                 |
                 | SQL Query
                 v
          +-------------+
          |    MySQL    |
          | (esp32_db)  |
          +-------------+
✨ คุณสมบัติที่สำคัญ (Features)🌡 Monitoring: แสดงผลด้วย JustGage ที่ทันสมัยและกราฟประวัติข้อมูลล่าสุด 10 รายการ🛡 Security: ระบบตรวจสอบ API Key ก่อนบันทึกข้อมูล และป้องกัน SQL Injection ⚙️ WiFi Manager: ตั้งค่าการเชื่อมต่อ WiFi ผ่านหน้าเว็บ Captive Portal (ไม่ต้องฝังรหัสผ่านในโค้ด) 🔄 Edge Computing: ระบบรดน้ำและแจ้งเตือนแก๊สทำงานได้ทันทีแม้เน็ตหลุด 📱 Notifications: ส่งข้อความแจ้งเตือนเข้ามือถือผ่าน Telegram ทันทีเมื่อค่าผิดปกติ 🛠 รายละเอียดอุปกรณ์และซอฟต์แวร์ (Tech Stack)ส่วนประกอบรายละเอียดMicrocontrollerESP32 (DevKit V1) SensorsDHT22, MQ-135, Soil Moisture Sensor ActuatorsRelay (Water Pump), Active Buzzer BackendPHP 7.4+ / XAMPPDatabaseMySQL (MariaDB) FrontendBootstrap 5, JustGage (JavaScript)📦 ขั้นตอนการติดตั้ง (Installation)1️⃣ การตั้งค่าฐานข้อมูล (Database Setup)นำคำสั่งด้านล่างไปรันใน phpMyAdmin:SQLCREATE DATABASE esp32_db;
USE esp32_db;

-- ตารางเก็บข้อมูลเซนเซอร์
CREATE TABLE sensor_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    temperature FLOAT,
    humidity FLOAT,
    gas_level INT,
    soil_moisture INT,
    relay_status VARCHAR(5),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ตารางควบคุมอุปกรณ์
CREATE TABLE control_panel (
    id INT PRIMARY KEY,
    status INT DEFAULT 0
);
INSERT INTO control_panel (id, status) VALUES (1, 0);
2️⃣ การตั้งค่าเซิร์ฟเวอร์ (Web Server)นำไฟล์ index.php, post-data.php และ config.php ไปไว้ใน C:\xampp\htdocs\smartfarm\แก้ไขไฟล์ config.php เพื่อตั้งค่ารหัสผ่านฐานข้อมูลให้ตรงกับเครื่องของคุณสำคัญ: ตั้งค่า api_key ในไฟล์ post-data.php ให้ตรงกับในโค้ด ESP32 เพื่อความปลอดภัย3️⃣ การตั้งค่า ESP32 (Firmware)เปิดไฟล์ esp32.ino ด้วย Arduino IDEติดตั้ง Library: WiFiManager, UniversalTelegramBot, DHT sensor libraryแก้ไข serverName ให้เป็น IP ของคอมพิวเตอร์คุณ (เช่น http://192.168.1.50/smartfarm/post-data.php)📁 โครงสร้างโปรเจกต์ (Project Structure)Plaintextsmartfarm-iot/
│
├── Firmware/
│   └── esp32/
│       ├── esp32.ino        # โค้ดหลักบนบอร์ด ESP32
│       └── config.h         # ตั้งค่ารหัสผ่านและ Token ต่างๆ
│
├── Web/
│   ├── config.php           # ตั้งค่าการเชื่อมต่อฐานข้อมูล
│   ├── index.php            # หน้า Dashboard และปุ่มควบคุม
│   └── post-data.php        # API รับข้อมูลจาก ESP32
│
├── screenshots/             # รูปภาพการทำงานของระบบ
└── README.md                # เอกสารประกอบโครงงาน
🔒 หมายเหตุเรื่องความปลอดภัย (Security Notes)User Account Control (UAC): จากการแจ้งเตือนในระบบ แนะนำให้ติดตั้ง XAMPP ไว้ที่ C:\xampp เพื่อหลีกเลี่ยงปัญหา Permission ของ WindowsAPI Key: ระบบจะปฏิเสธข้อมูลทุกประเภทที่ไม่มี API Key ที่ถูกต้องส่งมาด้วย