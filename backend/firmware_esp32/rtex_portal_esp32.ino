/* =========================================================================
 * RTEX SMART GATE - FIRMWARE IOT ESP32 RFID / NFC (e-KTP)
 * =========================================================================
 * Sistem Akses Portal Perumahan & Registrasi e-KTP Berbasis IoT
 * Backend : RTEX (Sistem Administrasi Warga Digital)
 * Server  : https://rtex.unique-monsters.my.id
 *
 * KOMPATIBEL DENGAN:
 * 1. Arduino Nano ESP32 (ESP32-S3)
 * 2. ESP32 DevKit Standar (30 pin / 38 pin ESP-WROOM-32 / NodeMCU-32S)
 *
 * =========================================================================
 * TABEL WIRING / PENGKABELAN:
 * =========================================================================
 * Wiring RC522 ke Arduino Nano ESP32:
 *   RC522   | Pin Arduino Nano ESP32
 *   --------|-----------------------
 *   SDA(SS) | Pin D10 (atau GPIO 21)
 *   SCK     | Pin D13 (atau GPIO 48)
 *   MOSI    | Pin D11 (atau GPIO 38)
 *   MISO    | Pin D12 (atau GPIO 47)
 *   RST     | Pin D9  (atau GPIO 18)
 *   GND     | GND
 *   3.3V    | 3V3    ⚠️ (PERINGATAN: Jangan ke 5V / VBUS!)
 *
 *   Aktuator / Indikator Arduino Nano ESP32:
 *   Servo   | Pin D6
 *   Relay   | Pin D7
 *   Buzzer  | Pin D5
 *   LED Hijau | Pin D4 (Akses Diterima)
 *   LED Merah | Pin D3 (Akses Ditolak)
 *
 * -------------------------------------------------------------------------
 * Wiring RC522 ke ESP32 Standar (DevKit 30/38 pin):
 *   RC522   | Pin ESP32 DevKit
 *   --------|-----------------
 *   SDA(SS) | GPIO 5
 *   SCK     | GPIO 18
 *   MOSI    | GPIO 23
 *   MISO    | GPIO 19
 *   RST     | GPIO 22
 *   GND     | GND
 *   3.3V    | 3.3V   ⚠️ (PERINGATAN: Jangan ke 5V / VIN!)
 *
 *   Aktuator / Indikator ESP32 DevKit:
 *   Servo   | GPIO 13
 *   Relay   | GPIO 12
 *   Buzzer  | GPIO 14
 *   LED Hijau | GPIO 26 (Akses Diterima)
 *   LED Merah | GPIO 27 (Akses Ditolak)
 * ========================================================================= */

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <SPI.h>
#include <MFRC522.h>

// -------------------------------------------------------------------------
// PILIHAN BOARD (Hapus komentar salah satu jika ingin memilih manual,
// atau biarkan deteksi otomatis saat memilih board di Arduino IDE):
// -------------------------------------------------------------------------
// #define USE_NANO_ESP32
// #define USE_DEVKIT_ESP32

#if defined(ARDUINO_NANO_ESP32) || defined(USE_NANO_ESP32)
  // Pinout Arduino Nano ESP32
  #define BOARD_NAME      "Arduino Nano ESP32"
  #define PIN_SS          D10
  #define PIN_RST         D9
  #define PIN_SERVO       D6
  #define PIN_RELAY       D7
  #define PIN_BUZZER      D5
  #define PIN_LED_GREEN   D4
  #define PIN_LED_RED     D3
#else
  // Pinout ESP32 Standar DevKit (30 / 38 pin)
  #define BOARD_NAME      "ESP32 DevKit Standar"
  #define PIN_SS          5
  #define PIN_RST         22
  #define PIN_SERVO       13
  #define PIN_RELAY       12
  #define PIN_BUZZER      14
  #define PIN_LED_GREEN   26
  #define PIN_LED_RED     27
#endif

// Library Servo: Support Arduino Nano ESP32 (Servo.h bawaan) & ESP32 DevKit (Native PWM bawaan)
// TIDAK MEMERLUKAN library tambahan ESP32Servo sama sekali!
#if defined(ARDUINO_NANO_ESP32)
  #include <Servo.h>
#else
  // Driver Servo Native ESP32 PWM (Built-in tanpa perlu install library eksternal)
  class NativeGateServo {
  private:
    int _pin = -1;
    int _channel = 0;
  public:
    void attach(int pin, int channel = 0) {
      _pin = pin;
      _channel = channel;
      #if defined(ESP_ARDUINO_VERSION) && ESP_ARDUINO_VERSION >= ESP_ARDUINO_VERSION_VAL(3, 0, 0)
        ledcAttach(_pin, 50, 16);
      #else
        ledcSetup(_channel, 50, 16);
        ledcAttachPin(_pin, _channel);
      #endif
      write(0);
    }
    void write(int angle) {
      if (_pin < 0) return;
      angle = constrain(angle, 0, 180);
      int duty = map(angle, 0, 180, 1638, 8192);
      #if defined(ESP_ARDUINO_VERSION) && ESP_ARDUINO_VERSION >= ESP_ARDUINO_VERSION_VAL(3, 0, 0)
        ledcWrite(_pin, duty);
      #else
        ledcWrite(_channel, duty);
      #endif
    }
  };
  #define Servo NativeGateServo
#endif

// =========================================================================
// KONFIGURASI WIFI & SERVER RTEX (SESUAIKAN DENGAN JARINGAN ANDA)
// =========================================================================
const char* WIFI_SSID     = ":3";       // Ganti dengan SSID WiFi Anda
const char* WIFI_PASSWORD = "Penyada9";     // Ganti dengan Password WiFi Anda

// Endpoint Backend RTEX API
const char* SERVER_URL    = "https://rtex.unique-monsters.my.id/api/iot/gate/tap";
const char* API_KEY       = "rtex-gate-key-2026";
const char* DEVICE_ID     = "PORTAL-UTAMA-RT05";

// Objek RFID & Servo
MFRC522 rfid(PIN_SS, PIN_RST);
Servo gateServo;

// Variabel Waktu Cooldown agar kartu tidak terbaca berulang seketika
unsigned long lastTapTime = 0;
const unsigned long TAP_COOLDOWN_MS = 2500;

// Sudut Gerakan Servo Palang
const int SERVO_CLOSED_ANGLE = 0;   // Posisi palang tertutup datar
const int SERVO_OPENED_ANGLE = 90;  // Posisi palang naik terbuka tegak

// Forward declarations fungsi helper
void connectToWiFi();
void sendTapToServer(String uid);
void indicateAccessGranted(String userName, String houseNo);
void indicateRegistrationSuccess(String msg);
void indicateAccessDenied(String reason);
void beep(int durationMs);
String parseJsonValue(const String& json, const String& key);
bool parseJsonBool(const String& json, const String& key);

void setup() {
  Serial.begin(115200);
  delay(1200);

  Serial.println("\n==============================================");
  Serial.println("   RTEX SMART GATE IOT CONTROLLER - ESP32     ");
  Serial.print("   Tipe Board Terdeteksi: ");
  Serial.println(BOARD_NAME);
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

  // Inisialisasi Servo Palang Pintu
  gateServo.attach(PIN_SERVO);
  gateServo.write(SERVO_CLOSED_ANGLE);

  // Inisialisasi SPI & RFID RC522
  SPI.begin();
  rfid.PCD_Init();
  delay(100);

  Serial.print("Sensor RFID-RC522 pada Pin SS=");
  Serial.print(PIN_SS);
  Serial.print(", RST=");
  Serial.println(PIN_RST);
  rfid.PCD_DumpVersionToSerial();

  // Koneksi ke WiFi
  connectToWiFi();

  // Beep 1x tanda sistem siap
  beep(100);
  Serial.println("✓ Sistem Smart Gate Siap! Silakan tempelkan e-KTP.");
}

void loop() {
  // Cek dan pulihkan koneksi WiFi otomatis jika terputus
  if (WiFi.status() != WL_CONNECTED) {
    connectToWiFi();
  }

  // Cek apakah ada kartu e-KTP / NFC di dekat sensor
  if (!rfid.PICC_IsNewCardPresent()) {
    return;
  }

  // Baca serial UID kartu
  if (!rfid.PICC_ReadCardSerial()) {
    return;
  }

  // Filter cooldown agar 1 kali tap tidak terhitung ganda
  if (millis() - lastTapTime < TAP_COOLDOWN_MS) {
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    return;
  }
  lastTapTime = millis();

  // Konversi UID byte array menjadi format Hexadesimal (Contoh: "4A:2F:89:1B")
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
  Serial.print("💳 Kartu e-KTP Terdeteksi! UID: ");
  Serial.println(uidString);

  // Beep pendek saat kartu pertama kali disentuh
  beep(60);

  // Kirim data UID ke Server RTEX
  sendTapToServer(uidString);

  // Hentikan komunikasi dengan kartu saat ini
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
}

// -------------------------------------------------------------------------
// FUNGSI MENGHUBUNGKAN KE WIFI
// -------------------------------------------------------------------------
void connectToWiFi() {
  if (WiFi.status() == WL_CONNECTED) return;

  Serial.print("Menghubungkan ke WiFi [");
  Serial.print(WIFI_SSID);
  Serial.print("]");

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 25) {
    delay(500);
    Serial.print(".");
    attempts++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✓ WiFi Berhasil Terhubung!");
    Serial.print("IP Address ESP32: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\n⚠️ Gagal terhubung ke WiFi. Periksa SSID & Password.");
  }
}

// -------------------------------------------------------------------------
// FUNGSI MENGIRIM DATA TAP KE SERVER RTEX
// -------------------------------------------------------------------------
void sendTapToServer(String uid) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("Error: Tidak ada koneksi internet!");
    indicateAccessDenied("Tidak Ada Koneksi Internet");
    return;
  }

  WiFiClientSecure client;
  client.setInsecure(); // Mengabaikan verifikasi sertifikat SSL untuk kemudahan ESP32

  HTTPClient http;
  http.begin(client, SERVER_URL);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-KEY", API_KEY);

  // Buat Payload JSON sederhana tanpa dependensi library eksternal
  String jsonPayload = "{\"uid\":\"" + uid + "\",\"device_id\":\"" + String(DEVICE_ID) + "\",\"mode\":\"auto\"}";

  Serial.print("Mengirim ke server: ");
  Serial.println(jsonPayload);

  int httpCode = http.POST(jsonPayload);

  if (httpCode > 0) {
    String responseString = http.getString();
    Serial.print("HTTP Response: ");
    Serial.println(httpCode);
    Serial.println(responseString);

    bool success  = parseJsonBool(responseString, "success");
    bool openGate = parseJsonBool(responseString, "open_gate");
    String mode   = parseJsonValue(responseString, "mode");
    String msg    = parseJsonValue(responseString, "message");

    // 1. MODE REGISTRASI (User sedang klik 'Mulai Scan KTP' di Web)
    if (mode == "registration" && success) {
      Serial.println("\n==============================================");
      Serial.println("🎉 MODE REGISTRASI: e-KTP BERHASIL DIDAFTARKAN!");
      Serial.println("==============================================");
      indicateRegistrationSuccess(msg);
    }
    // 2. MODE AKSES GERBANG: AKSES DITERIMA (BUKA PALANG PORTAL)
    else if (openGate || (success && mode == "gate_access")) {
      String userName = parseJsonValue(responseString, "user_name");
      String houseNo  = parseJsonValue(responseString, "house_number");
      if (userName == "") userName = "Warga RT";

      Serial.println("\n==============================================");
      Serial.print("🟢 AKSES DITERIMA! Selamat Datang, ");
      Serial.print(userName);
      Serial.print(" (Rumah: ");
      Serial.print(houseNo);
      Serial.println(")");
      Serial.println("==============================================");

      indicateAccessGranted(userName, houseNo);
    }
    // 3. AKSES DITOLAK (KTP BELUM TERDAFTAR / DINONAKTIFKAN)
    else {
      Serial.println("\n==============================================");
      Serial.print("🔴 AKSES DITOLAK! Alasan: ");
      Serial.println(msg != "" ? msg : "KTP Tidak Terdaftar");
      Serial.println("==============================================");

      indicateAccessDenied(msg != "" ? msg : "Akses Ditolak");
    }
  } else {
    Serial.print("⚠️ Gagal menghubungi server: ");
    Serial.println(http.errorToString(httpCode));
    indicateAccessDenied("Koneksi Server Gagal");
  }

  http.end();
}

// -------------------------------------------------------------------------
// RESPON AKSI: AKSES DITERIMA (BUKA PORTAL)
// -------------------------------------------------------------------------
void indicateAccessGranted(String userName, String houseNo) {
  digitalWrite(PIN_LED_GREEN, HIGH);
  digitalWrite(PIN_LED_RED, LOW);

  // Beep gembira 2x
  beep(80);
  delay(80);
  beep(120);

  // Buka Palang Portal (Servo bergerak ke 90 derajat & Relay ON)
  Serial.println("[PORTAL] Membuka palang portal (90 derajat)...");
  gateServo.write(SERVO_OPENED_ANGLE);
  digitalWrite(PIN_RELAY, HIGH);

  // Tunggu selama 5 detik agar kendaraan/warga lewat
  delay(5000);

  // Tutup kembali palang portal
  Serial.println("[PORTAL] Menutup kembali palang portal...");
  gateServo.write(SERVO_CLOSED_ANGLE);
  digitalWrite(PIN_RELAY, LOW);

  digitalWrite(PIN_LED_GREEN, LOW);
}

// -------------------------------------------------------------------------
// RESPON AKSI: REGISTRASI e-KTP BERHASIL
// -------------------------------------------------------------------------
void indicateRegistrationSuccess(String msg) {
  digitalWrite(PIN_LED_RED, LOW);

  // Kedipkan LED Hijau 3x dan Beep nada registrasi sukses
  for (int i = 0; i < 3; i++) {
    digitalWrite(PIN_LED_GREEN, HIGH);
    digitalWrite(PIN_BUZZER, HIGH);
    delay(100);
    digitalWrite(PIN_LED_GREEN, LOW);
    digitalWrite(PIN_BUZZER, LOW);
    delay(100);
  }
}

// -------------------------------------------------------------------------
// RESPON AKSI: AKSES DITOLAK (PORTAL TERKUNCI)
// -------------------------------------------------------------------------
void indicateAccessDenied(String reason) {
  digitalWrite(PIN_LED_GREEN, LOW);

  // 3x Beep panjang peringatan & LED Merah berkedip
  for (int i = 0; i < 3; i++) {
    digitalWrite(PIN_LED_RED, HIGH);
    digitalWrite(PIN_BUZZER, HIGH);
    delay(200);
    digitalWrite(PIN_LED_RED, LOW);
    digitalWrite(PIN_BUZZER, LOW);
    delay(100);
  }
}

// -------------------------------------------------------------------------
// HELPER: BUZZER BEEP
// -------------------------------------------------------------------------
void beep(int durationMs) {
  digitalWrite(PIN_BUZZER, HIGH);
  delay(durationMs);
  digitalWrite(PIN_BUZZER, LOW);
}

// -------------------------------------------------------------------------
// LIGHTWEIGHT JSON PARSER (Tidak memerlukan library tambahan eksternal)
// -------------------------------------------------------------------------
String parseJsonValue(const String& json, const String& key) {
  String target = "\"" + key + "\":\"";
  int startIdx = json.indexOf(target);
  if (startIdx == -1) {
    target = "\"" + key + "\": \"";
    startIdx = json.indexOf(target);
  }
  if (startIdx == -1) return "";

  startIdx += target.length();
  int endIdx = json.indexOf("\"", startIdx);
  if (endIdx == -1) return "";

  return json.substring(startIdx, endIdx);
}

bool parseJsonBool(const String& json, const String& key) {
  String target = "\"" + key + "\":";
  int startIdx = json.indexOf(target);
  if (startIdx == -1) {
    target = "\"" + key + "\": ";
    startIdx = json.indexOf(target);
  }
  if (startIdx == -1) return false;

  int valStart = startIdx + target.length();
  while (valStart < json.length() && (json[valStart] == ' ' || json[valStart] == '\t')) {
    valStart++;
  }

  if (json.substring(valStart).startsWith("true")) {
    return true;
  }
  return false;
}
