/* =========================================================================
 * RTEX SMART GATE - FIRMWARE ESP32 RFID / NFC (e-KTP)
 * =========================================================================
 * Sistem Akses Portal Perumahan & Registrasi e-KTP Berbasis IoT
 * Backend: RTEX (Sistem Administrasi Warga Digital)
 * Domain: https://rtex.unique-monsters.my.id
 *
 * PUSTAKA (LIBRARY) ARDUINO YANG DIBUTUHKAN:
 * 1. MFRC522 by GithubCommunity (untuk RFID-RC522 SPI)
 * 2. ArduinoJson by Benoit Blanchon (versi 6.x atau 7.x)
 * 3. ESP32Servo by Kevin Harrington (untuk motor servo palang pintu)
 *
 * SKEMA KONEKSI PIN:
 * -------------------------------------------------------------------------
 * RFID RC522          ESP32 DevKit
 * -------------------------------------------------------------------------
 * SDA (SS)      --->  GPIO 5
 * SCK           --->  GPIO 18
 * MOSI          --->  GPIO 23
 * MISO          --->  GPIO 19
 * RST           --->  GPIO 22
 * GND           --->  GND
 * 3.3V          --->  3V3 (PERINGATAN: JANGAN GUNAKAN PIN 5V!)
 * -------------------------------------------------------------------------
 * PERANGKAT LAIN      ESP32 DevKit
 * -------------------------------------------------------------------------
 * Servo Signal  --->  GPIO 13 (PWM Servo Palang Portal)
 * Relay Signal  --->  GPIO 12 (Active HIGH/LOW untuk Solenoid / Barrier Gate)
 * Buzzer (+)    --->  GPIO 14
 * LED Hijau (+) --->  GPIO 26 (Akses Diterima)
 * LED Merah (+) --->  GPIO 27 (Akses Ditolak)
 * ========================================================================= */

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ESP32Servo.h>
#include <ArduinoJson.h>

// --- KONFIGURASI JARINGAN WIFI & SERVER RTEX ---
const char* WIFI_SSID     = "NAMA_WIFI_RT_ANDA";       // Ganti dengan SSID WiFi Anda
const char* WIFI_PASSWORD = "PASSWORD_WIFI_ANDA";     // Ganti dengan Password WiFi Anda

// Endpoint Backend RTEX API
const char* SERVER_URL    = "https://rtex.unique-monsters.my.id/api/iot/gate/tap";
const char* API_KEY       = "rtex-gate-key-2026";
const char* DEVICE_ID     = "PORTAL-UTAMA-RT05";

// --- DEFINISI PIN HARDWARE ---
#define PIN_SS          5
#define PIN_RST         22
#define PIN_SERVO       13
#define PIN_RELAY       12
#define PIN_BUZZER      14
#define PIN_LED_GREEN   26
#define PIN_LED_RED     27

// --- OBJEK PERANGKAT ---
MFRC522 rfid(PIN_SS, PIN_RST);
Servo gateServo;

// Waktu cooldown agar 1 kartu tidak terbaca berkali-kali secara instan
unsigned long lastTapTime = 0;
const unsigned long TAP_COOLDOWN_MS = 2500;

// Posisi Derajat Servo Palang
const int SERVO_CLOSED_ANGLE = 0;   // Palang tertutup
const int SERVO_OPENED_ANGLE = 90;  // Palang terbuka tegak

void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("\n==============================================");
  Serial.println("   RTEX SMART GATE IOT CONTROLLER - ESP32     ");
  Serial.println("==============================================");

  // Inisialisasi Pin Output
  pinMode(PIN_BUZZER, OUTPUT);
  pinMode(PIN_LED_GREEN, OUTPUT);
  pinMode(PIN_LED_RED, OUTPUT);
  pinMode(PIN_RELAY, OUTPUT);

  digitalWrite(PIN_BUZZER, LOW);
  digitalWrite(PIN_LED_GREEN, LOW);
  digitalWrite(PIN_LED_RED, LOW);
  digitalWrite(PIN_RELAY, LOW); // Relay Standby OFF

  // Inisialisasi Servo
  gateServo.attach(PIN_SERVO);
  gateServo.write(SERVO_CLOSED_ANGLE);

  // Inisialisasi Bus SPI dan RFID Reader
  SPI.begin();
  rfid.PCD_Init();
  delay(100);
  rfid.PCD_DumpVersionToSerial();
  Serial.println("Sensor RFID-RC522 Siap.");

  // Hubungkan ke WiFi
  connectToWiFi();

  // Beep 1x tanda sistem siap
  beep(100);
  Serial.println("Sistem Smart Gate Aktif. Silakan tempelkan e-KTP.");
}

void loop() {
  // Cek dan pulihkan koneksi WiFi jika terputus
  if (WiFi.status() != WL_CONNECTED) {
    connectToWiFi();
  }

  // Cek apakah ada kartu e-KTP baru di depan sensor
  if (!rfid.PICC_IsNewCardPresent()) {
    return;
  }

  // Baca serial UID kartu
  if (!rfid.PICC_ReadCardSerial()) {
    return;
  }

  // Cooldown filter
  if (millis() - lastTapTime < TAP_COOLDOWN_MS) {
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    return;
  }
  lastTapTime = millis();

  // Ubah UID byte array menjadi format Hex String (Contoh: "4A:2F:89:1B")
  String uidString = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (i > 0) uidString += ":";
    if (rfid.uid.uidByte[i] < 0x10) {
      uidString += "0";
    }
    uidString += String(rfid.uid.uidByte[i], HEX);
  }
  uidString.toUpperCase();

  Serial.println("\n----------------------------------------------");
  Serial.print("Kartu e-KTP Terdeteksi! UID: ");
  Serial.println(uidString);

  // Beep pendek 1x saat kartu ditempel
  beep(60);

  // Kirim data UID ke Server RTEX
  sendTapToServer(uidString);

  // Selesai baca kartu
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
}

// Fungsi Menghubungkan ke Jaringan WiFi
void connectToWiFi() {
  Serial.print("Menghubungkan ke WiFi: ");
  Serial.println(WIFI_SSID);

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 25) {
    delay(500);
    Serial.print(".");
    attempts++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi Terhubung!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\nGagal terhubung ke WiFi. Berjalan dalam mode offline.");
  }
}

// Fungsi Mengirim Data Tap ke REST API RTEX
void sendTapToServer(String uid) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("Error: Tidak ada koneksi WiFi.");
    indicateAccessDenied("Tidak Ada Koneksi Internet");
    return;
  }

  WiFiClientSecure client;
  client.setInsecure(); // Mengabaikan validasi sertifikat SSL (HTTPS)

  HTTPClient http;
  http.begin(client, SERVER_URL);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-KEY", API_KEY);

  // Format payload JSON
  StaticJsonDocument<256> docRequest;
  docRequest["uid"] = uid;
  docRequest["device_id"] = DEVICE_ID;
  docRequest["mode"] = "auto"; // Bisa untuk pendaftaran scan KTP & buka portal

  String jsonPayload;
  serializeJson(docRequest, jsonPayload);

  Serial.print("Mengirim payload: ");
  Serial.println(jsonPayload);

  int httpCode = http.POST(jsonPayload);

  if (httpCode > 0) {
    String responseString = http.getString();
    Serial.print("HTTP Response Code: ");
    Serial.println(httpCode);
    Serial.print("Response: ");
    Serial.println(responseString);

    StaticJsonDocument<512> docResponse;
    DeserializationError error = deserializeJson(docResponse, responseString);

    if (!error) {
      bool success = docResponse["success"] | false;
      bool openGate = docResponse["open_gate"] | false;
      const char* mode = docResponse["mode"] | "gate_access";
      const char* message = docResponse["message"] | "";

      // 1. MODE REGISTRASI KTP (Saat warga/admin menekan 'Mulai Scan KTP' di Web)
      if (String(mode) == "registration" && success) {
        Serial.println(">> MODE REGISTRASI: e-KTP BERHASIL DIDAFTARKAN! <<");
        indicateRegistrationSuccess(message);
      }
      // 2. MODE AKSES GERBANG: AKSES DITERIMA (BUKA PORTAL)
      else if (openGate) {
        const char* userName = docResponse["user_name"] | "Warga";
        const char* houseNo  = docResponse["house_number"] | "-";
        Serial.print(">> AKSES DITERIMA! Selamat Datang: ");
        Serial.print(userName);
        Serial.print(" (No. ");
        Serial.print(houseNo);
        Serial.println(") <<");

        indicateAccessGranted();
      }
      // 3. AKSES DITOLAK (KTP BELUM TERDAFTAR / DINONAKTIFKAN)
      else {
        Serial.print(">> AKSES DITOLAK! Alasan: ");
        Serial.print(message);
        Serial.println(" <<");

        indicateAccessDenied(message);
      }
    } else {
      Serial.println("Error parsing JSON dari server.");
      indicateAccessDenied("Error Respons Server");
    }
  } else {
    Serial.print("Error koneksi HTTP: ");
    Serial.println(http.errorToString(httpCode));
    indicateAccessDenied("Koneksi Server Gagal");
  }

  http.end();
}

// Efek Akses Diterima (Buka Portal, LED Hijau, Beep Ceria 2x)
void indicateAccessGranted() {
  digitalWrite(PIN_LED_GREEN, HIGH);
  digitalWrite(PIN_LED_RED, LOW);

  // Beep 2x ceria
  beep(80);
  delay(80);
  beep(120);

  // Buka Portal (Servo bergerak 90 derajat & Relay ON)
  Serial.println("[PORTAL] Membuka palang portal...");
  gateServo.write(SERVO_OPENED_ANGLE);
  digitalWrite(PIN_RELAY, HIGH);

  // Tunggu portal terbuka selama 5 detik
  delay(5000);

  // Tutup kembali portal
  Serial.println("[PORTAL] Menutup kembali palang portal...");
  gateServo.write(SERVO_CLOSED_ANGLE);
  digitalWrite(PIN_RELAY, LOW);

  digitalWrite(PIN_LED_GREEN, LOW);
}

// Efek Registrasi e-KTP Berhasil di Web
void indicateRegistrationSuccess(const char* msg) {
  // LED Hijau berkedip 3x dan Beep nada gembira
  for (int i = 0; i < 3; i++) {
    digitalWrite(PIN_LED_GREEN, HIGH);
    digitalWrite(PIN_BUZZER, HIGH);
    delay(100);
    digitalWrite(PIN_LED_GREEN, LOW);
    digitalWrite(PIN_BUZZER, LOW);
    delay(100);
  }
}

// Efek Akses Ditolak (Portal Tetap Terkunci, LED Merah Berkedip, Beep Peringatan 3x)
void indicateAccessDenied(const char* reason) {
  digitalWrite(PIN_LED_GREEN, LOW);

  // 3x Beep panjang peringatan
  for (int i = 0; i < 3; i++) {
    digitalWrite(PIN_LED_RED, HIGH);
    digitalWrite(PIN_BUZZER, HIGH);
    delay(200);
    digitalWrite(PIN_LED_RED, LOW);
    digitalWrite(PIN_BUZZER, LOW);
    delay(100);
  }
}

// Helper Beep Buzzer
void beep(int durationMs) {
  digitalWrite(PIN_BUZZER, HIGH);
  delay(durationMs);
  digitalWrite(PIN_BUZZER, LOW);
}
