import { SerialPort } from "serialport";
import { ReadlineParser } from "@serialport/parser-readline";
import fetchRetry from "fetch-retry";
import { v4 as uuidv4 } from "uuid";

const fetch = fetchRetry(global.fetch, {
    retries: 5,
    retryDelay: 1000,
});

const PULSE_1_LINE = "+"; // Coin acceptor: 1 pulse = 1 PHP
const PULSE_10_LINE = "0"; // Bill acceptor: 1 pulse = 10 PHP
const PULSE_ENDPOINT = "http://localhost:8000/api/pulsePayment";

let arduinoPath = null;
let callCount = 0;

for (const port of await SerialPort.list()) {
    if (port.friendlyName.includes("Arduino")) {
        arduinoPath = port.path;
    }
}
if (!arduinoPath) {
    console.error("Could not find Arduino COM port.");
    process.exit(1);
}

const port = new SerialPort({ path: arduinoPath, baudRate: 9600 }, (err) => {
    if (err) {
        console.error("Error while opening port: ", err.message);
        process.exit();
    }
});
console.log("Opened port at port:", arduinoPath);

function processLine(line) {
    line = line.trim();
    pulseValue = null;
    if (line == PULSE_1_LINE) {
        pulseValue = 1;
    } else if (line == PULSE_10_LINE) {
        pulseValue = 10;
    }
    if (!(pulseValue === null)) {
        callCount += 1;
        console.log("Line match found: ", line);
        // TODO: Add retries.
        fetch(PULSE_ENDPOINT, {
            method: "POST",
            body: JSON.stringify({
                id: uuidv4(),
                pulseValue: pulseValue,
            }),
        })
            .then(() => {
                console.log(`Called payment route (call count ${callCount})`);
            })
            .catch((err) => {
                console.error(
                    `Error encountered while calling payment route at callCount ${callCount}: `,
                    err
                );
            });
    } else {
        console.log("Line does not match:", line);
    }
}

// Use "\n" to be safe. Arduino ends lines with "\r\n" by default.
const parser = port.pipe(new ReadlineParser({ delimiter: "\n" }));
parser.on("data", processLine);
