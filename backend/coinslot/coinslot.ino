bool lastState_2 = HIGH;
bool lastState_3 = HIGH;

void setup() {
  pinMode(2, INPUT_PULLUP);
  pinMode(3, INPUT_PULLUP);
  Serial.begin(19200);
}

void loop() {
  bool curState_2 = digitalRead(2);
  bool curState_3 = digitalRead(3);

  // Coin slot pin
  if ((curState_2 == HIGH) && (lastState_2 == LOW)) {
    // Rising edge detected.
    Serial.println("+");
  }

  // Bill acceptor pin
  if ((curState_3 == HIGH) && (lastState_3 == LOW)) {
    // Rising edge detected.
    Serial.println("0");
  }

  lastState_2 = curState_2;
  lastState_3 = curState_3;
}