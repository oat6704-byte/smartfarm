#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiManager.h>
#include <WiFiClientSecure.h>
#include <UniversalTelegramBot.h> 
#include "DHT.h"
#include "config.h"
// --- ตั้งค่า Telegram ---

WiFiClientSecure client_secure;
UniversalTelegramBot bot(BOT_TOKEN, client_secure);

#define DHTPIN 13
#define DHTTYPE DHT22
DHT dht(DHTPIN, DHTTYPE);

#define MQ135_PIN 34
#define SOIL_PIN 32
#define RELAY_PIN 15

#define LED_WIFI 2    
#define LED_STATUS 4  
#define LED_WEB 0     
#define BUZZER_PIN 18

void setup() {
  Serial.begin(115200);
  dht.begin();
  
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(RELAY_PIN, OUTPUT);
  pinMode(LED_WIFI, OUTPUT);
  pinMode(LED_STATUS, OUTPUT);
  pinMode(LED_WEB, OUTPUT);
  
  digitalWrite(RELAY_PIN, LOW);
  digitalWrite(LED_WIFI, LOW);
  digitalWrite(LED_STATUS, LOW);
  digitalWrite(LED_WEB, LOW);

  WiFiManager wm;
  bool res = wm.autoConnect("ESP32_Config_AP"); 

  if(!res) {
      digitalWrite(LED_STATUS, HIGH); 
  } else {
      digitalWrite(LED_WIFI, HIGH); 
      client_secure.setInsecure(); // สำหรับ Telegram
      bot.sendMessage(CHAT_ID, "ระบบ MySQL เริ่มทำงาน!", "");
  }
}

void loop() {
  // --- 1. อ่านค่าเซนเซอร์ ---
  float h = dht.readHumidity();
  float t = dht.readTemperature();
  int gasVal = analogRead(MQ135_PIN);
  int soilVal = analogRead(SOIL_PIN); 
  
  // ตรวจสอบความถูกต้องของข้อมูลเซนเซอร์ก่อนเริ่มทำงาน
  if (isnan(h) || isnan(t)) {
    Serial.println("Failed to read from DHT sensor!");
    delay(2000); // รอสักพักแล้วเริ่มใหม่
    return; 
  }

  // --- 2. Edge Computing Logic (ทำงานได้ทันทีแม้เน็ตหลุด)  ---
  
  // ระบบปั๊มน้ำอัตโนมัติ
  String relayStateText = (soilVal > 3500) ? "ON" : "OFF";
  digitalWrite(RELAY_PIN, (soilVal > 3500) ? LOW : HIGH); // สมมติ Relay Active Low

  // ระบบแจ้งเตือนแก๊ส (Buzzer และ LED Status) [cite: 27, 29]
  if (gasVal > 1500) {
    tone(BUZZER_PIN, 1000); 
    digitalWrite(LED_STATUS, HIGH); 
  } else {
    noTone(BUZZER_PIN);
    digitalWrite(LED_STATUS, LOW);
  }

  // --- 3. ส่วนการเชื่อมต่อภายนอก (เมื่อมี WiFi เท่านั้น) [cite: 39] ---
  if (WiFi.status() == WL_CONNECTED) {
    digitalWrite(LED_WIFI, HIGH); //[cite: 31]

    // ส่ง Telegram เมื่อค่าถึงจุดวิกฤต [cite: 45]
    if (gasVal > 1500 || soilVal > 3500) {
       String message = "⚠️ แจ้งเตือนสถานะผิดปกติ\n";
       message += "ความชื้นในดิน: " + String(soilVal) + "\nแก๊ส: " + String(gasVal) ;
       bot.sendMessage(CHAT_ID, message, "");
    }

    // ส่งข้อมูลไป MySQL ผ่าน HTTP POST [cite: 41, 42]
    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String httpRequestData = String("api_key=lGFFoX4iAliV6L3") +
                             "&temp=" + String(t) + 
                             "&hum=" + String(h) + 
                             "&gas=" + String(gasVal) + 
                             "&soil=" + String(soilVal) + 
                             "&relay=" + relayStateText;
    
    int httpResponseCode = http.POST(httpRequestData);
    
    if (httpResponseCode > 0) {
      String payload = http.getString();
      // ควบคุม LED ผ่านหน้าเว็บ Dashboard [cite: 44]
      if (payload.indexOf("LED_ON") != -1) {
        digitalWrite(LED_WEB, HIGH);
      } else if (payload.indexOf("LED_OFF") != -1) {
        digitalWrite(LED_WEB, LOW);
      }
    }
    http.end();
  } else {
    digitalWrite(LED_WIFI, LOW); //[cite: 33]
  }
  
  delay(30000); // ส่งข้อมูลทุก 30 วินาที
}
  