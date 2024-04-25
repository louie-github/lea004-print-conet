For more details, please see the main repo README.

# Coin slot API
To run the backend coin slot interface, ensure that [Node](https://nodejs.org/en)
is installed, as well as npm. Ensure as well that your Arduino board is
properly set up and connected.

## Arduino code

Connect your Arduino to your computer and make sure that you have
uploaded the corresponding sketch to your board. If not, open
[Arduino IDE](https://www.arduino.cc/en/software/), open the sketch
[coinslot.ino](backend/coinslot/coinslot.ino), and upload it to your
board.

To connect your coin slot to the board, you can follow a similar circuit
diagram to the tutorial
[How to Control CH-926 Coin Acceptor With Arduino](https://www.instructables.com/How-to-Control-CH-926-Coin-Acceptor-With-Arduino/).

Make sure that you set `INTERRUPT_PIN` in the sketch to the pin to which
you connect your coin slot pulse wire. By default, this is set to
**pin 2**.

Insert coins while checking the Serial Monitor in Arduino IDE to make
sure that your coin slot is correctly being detected.

## JavaScript backend

Then, navigate to the [backend/coinslot](backend/coinslot/) directory
and install the project dependencies:
```bash
npm install
```

Then, start the coinslot API, which will automatically search for the
Arduino serial port and start listening for matching values.
```bash
node index.js
```

You can optionally adjust the code to match the pulse payment route in
the frontend web app:
```js
const PULSE_ENDPOINT = "http://localhost:8000/pulsePayment";
```