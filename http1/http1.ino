#include <ESP8266WiFi.h>
#include <ESP8266WebServer.h>
#include <DallasTemperature.h>
#include <OneWire.h>
#include <DHT.h>
#include <LiquidCrystal_I2C.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <ArduinoJson.h>

// ==== DEFINISI PIN & KONSTANTA ====
#define ONE_WIRE_BUS D5
#define DHTPIN D3
#define DHTTYPE DHT11
#define pump D6 // Relay aktif LOW

const int AirValue = 620;
const int WaterValue = 310;

// ==== VARIABEL ====
int soilMoistureValue = 0;
int soilmoist = 0;
int humi = 0, temp = 0;
int sistem = 1; // 1 = otomatis, 0 = manual
int pompaStatus = 0; // 1 = ON, 0 = OFF

// ==== INISIALISASI SENSOR & LCD ====
DHT dht(DHTPIN, DHTTYPE);
OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensors(&oneWire);
LiquidCrystal_I2C lcd(0x27, 16, 2);

// ==== KREDENSIAL WIFI ====
const char* ssid = "Nebeng";
const char* password = "26262626";

// ==== ALAMAT SERVER ====
const char* server_host = "192.168.1.3"; // Ganti sesuai IP server Anda

ESP8266WebServer server(80);

// ==== LOGIKA FUZZY SUGENO ====
float fuzzyKering(int x) {
  if (x <= 30) return 1;
  else if (x >= 50) return 0;
  else return (50 - x) / 20.0;
}

float fuzzyNormal(int x) {
  if (x < 30 || x > 70) return 0;
  else if (x <= 50) return (x - 30) / 20.0;
  else return (70 - x) / 20.0;
}

float fuzzyBasah(int x) {
  if (x <= 60) return 0;
  else if (x >= 80) return 1;
  else return (x - 60) / 20.0;
}

float fuzzySugenoPump(int soil) {
  float μ_kering = fuzzyKering(soil);
  float μ_normal = fuzzyNormal(soil);
  float μ_basah  = fuzzyBasah(soil);

  float atas = (μ_kering * 1.0) + (μ_normal * 0.0) + (μ_basah * 0.0);
  float bawah = μ_kering + μ_normal + μ_basah;

  if (bawah == 0) return 0;
  return atas / bawah;
}

// ==== HANDLER HTTP (untuk akses lokal/manual) ====
void addCORSHeaders() {
  server.sendHeader("Access-Control-Allow-Origin", "*");
  server.sendHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
  server.sendHeader("Access-Control-Allow-Headers", "Content-Type");
  server.sendHeader("Access-Control-Allow-Credentials", "true");
}

void handleRoot() {
  addCORSHeaders();
  String html = "<html><head><title>Smart Garden</title></head><body>";
  html += "<h1>Smart Garden Control</h1>";
  html += "<p>Mode: " + String(sistem ? "AUTO" : "MANUAL") + "</p>";
  html += "<p>Pump Status: " + String(pompaStatus ? "ON" : "OFF") + "</p>";
  html += "<p><a href=\"/auto/on\">Auto ON</a> | <a href=\"/auto/off\">Auto OFF</a></p>";
  html += "<p><a href=\"/pump/on\">Pump ON</a> | <a href=\"/pump/off\">Pump OFF</a></p>";
  html += "<p><a href=\"/status\">Sensor Status</a></p>";
  html += "</body></html>";
  server.send(200, "text/html", html);
}

void handleAutoOn() {
  addCORSHeaders();
  sistem = 1;
  server.send(200, "text/plain", "Mode AUTO diaktifkan");
}

void handleAutoOff() {
  addCORSHeaders();
  sistem = 0;
  server.send(200, "text/plain", "Mode MANUAL diaktifkan");
}

void handlePumpOn() {
  addCORSHeaders();
  if (sistem == 0) {
    digitalWrite(pump, LOW); // Relay aktif LOW
    pompaStatus = 1;
    lcd.setCursor(13, 1);
    lcd.print("ON ");
    server.send(200, "text/plain", "Pompa ON");
  } else {
    server.send(200, "text/plain", "Pompa dikontrol otomatis");
  }
}

void handlePumpOff() {
  addCORSHeaders();
  if (sistem == 0) {
    digitalWrite(pump, HIGH); // Relay aktif LOW
    pompaStatus = 0;
    lcd.setCursor(13, 1);
    lcd.print("OFF");
    server.send(200, "text/plain", "Pompa OFF");
  } else {
    server.send(200, "text/plain", "Pompa dikontrol otomatis");
  }
}

void handleStatus() {
  addCORSHeaders();
  String json = "{";
  json += "\"temperature\":" + String(temp) + ",";
  json += "\"soilMoisture\":" + String(soilmoist) + ",";
  json += "\"humidity\":" + String(humi) + ",";
  json += "\"mode\":" + String(sistem) + ",";
  json += "\"pumpStatus\":" + String(pompaStatus);
  json += "}";
  server.send(200, "application/json", json);
}

void setup() {
  Serial.begin(9600);
  sensors.begin();
  dht.begin();
  pinMode(pump, OUTPUT);
  digitalWrite(pump, HIGH); // Pompa OFF default (relay aktif LOW)

  lcd.begin(16, 2);
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0, 0);
  lcd.print("  Smart Garden   ");
  lcd.setCursor(0, 1);
  lcd.print("    SIRAMIN   ");
  delay(2000);
  lcd.clear();

  lcd.print("Mst=   %, T=   C");
  lcd.setCursor(0, 1);
  lcd.print("Hum=   %, P=  ");

  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("Connected! IP address: ");
  Serial.println(WiFi.localIP());

  server.on("/", handleRoot);
  server.on("/auto/on", handleAutoOn);
  server.on("/auto/off", handleAutoOff);
  server.on("/pump/on", handlePumpOn);
  server.on("/pump/off", handlePumpOff);
  server.on("/status", handleStatus);

  server.onNotFound([]() {
    if (server.method() == HTTP_OPTIONS) {
      addCORSHeaders();
      server.send(204);
    } else {
      server.send(404);
    }
  });

  server.begin();

  // Initial sensor read and LCD update
  sensors.requestTemperatures();
  temp = sensors.getTempCByIndex(0);

  soilMoistureValue = analogRead(A0);
  soilmoist = map(soilMoistureValue, AirValue, WaterValue, 0, 100);
  soilmoist = constrain(soilmoist, 0, 100);

  float h = dht.readHumidity();
  if (!isnan(h)) {
    humi = (int)h;
  }

  lcd.setCursor(12, 0);
  if (temp < 10) lcd.print(" ");
  lcd.print(temp);
  lcd.print(" ");

  lcd.setCursor(4, 0);
  if (soilmoist < 10) lcd.print("  ");
  else if (soilmoist < 100) lcd.print(" ");
  lcd.print(soilmoist);

  lcd.setCursor(4, 1);
  if (humi < 10) lcd.print("  ");
  else if (humi < 100) lcd.print(" ");
  lcd.print(humi);

  lcd.setCursor(13, 1);
  if (pompaStatus == 1) {
    lcd.print("ON ");
  } else {
    lcd.print("OFF");
  }
}

unsigned long previousSensorMillis = 0;
const long sensorInterval = 1000; // 1 detik untuk baca sensor & kirim data
unsigned long previousStatusMillis = 0;
const long statusInterval = 2000; // 2 detik untuk cek status dari web

void loop() {
  server.handleClient();

  // Reconnect WiFi if disconnected
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi disconnected. Reconnecting...");
    WiFi.disconnect();
    WiFi.begin(ssid, password);
    int retryCount = 0;
    while (WiFi.status() != WL_CONNECTED && retryCount < 20) {
      delay(500);
      Serial.print(".");
      retryCount++;
    }
    if (WiFi.status() == WL_CONNECTED) {
      Serial.println("Reconnected to WiFi");
    } else {
      Serial.println("Failed to reconnect to WiFi");
    }
  }

  unsigned long currentMillis = millis();

  // Ambil status mode & pompa dari web setiap 2 detik
  if (currentMillis - previousStatusMillis >= statusInterval) {
    previousStatusMillis = currentMillis;
    if (WiFi.status() == WL_CONNECTED) {
      HTTPClient http;
      String url = String("http://") + server_host + "/SG/get_status.php";
      WiFiClient client;
      http.begin(client, url);
      int httpCode = http.GET();
      if (httpCode == 200) {
        String payload = http.getString();
        DynamicJsonDocument doc(256);
        DeserializationError error = deserializeJson(doc, payload);
        if (!error) {
          String mode = doc["mode"];
          String status = doc["status"];
          sistem = (mode == "otomatis") ? 1 : 0;
          if (sistem == 0) {
            if (status == "ON") {
              digitalWrite(pump, LOW);
              pompaStatus = 1;
            } else {
              digitalWrite(pump, HIGH);
              pompaStatus = 0;
            }
          }
        }
      }
      http.end();
    }
  }

  // Baca sensor & kirim data ke server setiap 1 detik
  if (currentMillis - previousSensorMillis >= sensorInterval) {
    previousSensorMillis = currentMillis;

    sensors.requestTemperatures();
    temp = sensors.getTempCByIndex(0);

    soilMoistureValue = analogRead(A0);
    soilmoist = map(soilMoistureValue, AirValue, WaterValue, 0, 100);
    soilmoist = constrain(soilmoist, 0, 100);

    float h = dht.readHumidity();
    if (!isnan(h)) {
      humi = (int)h;
    }

const float fuzzyOnThreshold = 0.7;  // Ambang atas (pompa menyala)
const float fuzzyOffThreshold = 0.4; // Ambang bawah (pompa mati)

// Mode otomatis → fuzzy logic dengan hysteresis
if (sistem == 1) {
  float hasilFuzzy = fuzzySugenoPump(soilmoist);
  if (pompaStatus == 0 && hasilFuzzy >= fuzzyOnThreshold) {
    digitalWrite(pump, LOW);  // Pompa ON
    pompaStatus = 1;
    lcd.setCursor(13, 1);
    lcd.print("ON ");
  } else if (pompaStatus == 1 && hasilFuzzy <= fuzzyOffThreshold) {
    digitalWrite(pump, HIGH); // Pompa OFF
    pompaStatus = 0;
    lcd.setCursor(13, 1);
    lcd.print("OFF");
  }
}

    // Update LCD sensor values
    lcd.setCursor(12, 0);
    if (temp < 10) lcd.print(" ");
    lcd.print(temp);
    lcd.print(" ");

    lcd.setCursor(4, 0);
    if (soilmoist < 10) lcd.print("  ");
    else if (soilmoist < 100) lcd.print(" ");
    lcd.print(soilmoist);

    lcd.setCursor(4, 1);
    if (humi < 10) lcd.print("  ");
    else if (humi < 100) lcd.print(" ");
    lcd.print(humi);

    // Kirim data sensor ke server PHP
    if (WiFi.status() == WL_CONNECTED) {
      HTTPClient http;
      String serverPath = String("http://") + server_host + "/SG/input_data.php";
      serverPath += "?suhu_ds18b20=" + String(temp);
      serverPath += "&kelembaban_tanah=" + String(soilmoist);
      serverPath += "&suhu_dht11=" + String(humi); // Adjusted to send correct air temperature
      serverPath += "&kelembaban_dht11=" + String(humi);

      WiFiClient client;
      http.begin(client, serverPath);
      int httpResponseCode = http.GET();
      http.end();
    }

    // Debug serial
    Serial.print("Temp: "); Serial.println(temp);
    Serial.print("Soil: "); Serial.println(soilmoist);
    Serial.print("Humi: "); Serial.println(humi);
    Serial.print("Pompa: "); Serial.println(pompaStatus ? "ON" : "OFF");
    Serial.print("Mode: "); Serial.println(sistem ? "AUTO" : "MANUAL");
  }
}