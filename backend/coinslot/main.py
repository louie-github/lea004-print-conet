#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import serial
import serial.tools.list_ports

MARKER = "+"
PAYMENT_ROUTE = "http://localhost:8000/payment"


def find_arduino_port():
    for port in serial.tools.list_ports.comports():
        if "Arduino" in port.description:
            return port.device
    else:
        return False


def call_route(route=PAYMENT_ROUTE):
    print("Test!")
    # requests.get(route)


def main():
    port = find_arduino_port()
    if not port:
        raise FileNotFoundError("Could not find Arduino port.")

    print(f"Connected to Arduino at port: {port}")

    with serial.Serial(port, baudrate=9600) as ser:
        while True:
            line = ser.readline().decode("utf-8").strip()
            if line == MARKER:
                print("Pulse detected! Calling route.")
                call_route()
            else:
                print(f"Line does not match marker: {line}")
