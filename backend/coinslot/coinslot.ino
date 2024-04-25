const int INTERRUPT_PIN = 2;

volatile bool pulseDetected = false;

void handlePulse() {
  pulseDetected = true;
}

void setup() {
  pinMode(INTERRUPT_PIN, INPUT);
  attachInterrupt(digitalPinToInterrupt(INTERRUPT_PIN), handlePulse, RISING);
  Serial.begin(9600);
}

void loop() {
  if (pulseDetected) {
    pulseDetected = false;
    Serial.println("+");
  }
}