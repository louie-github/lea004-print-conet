#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import queue
import threading

import requests
import serial
import serial.tools.list_ports

MARKER = "+"
PAYMENT_ROUTE = "http://localhost:8000/pulsePayment"

CALL_QUEUE = queue.Queue()


def find_arduino_port():
    for port in serial.tools.list_ports.comports():
        if "Arduino" in port.description:
            return port.device
    else:
        return False


def route_caller():
    try:
        while True:
            item = CALL_QUEUE.get()
            print(f"Received item: {item}")
            requests.post(PAYMENT_ROUTE, json={"pulseValue": 1})
            CALL_QUEUE.task_done()
    except KeyboardInterrupt:
        CALL_QUEUE.task_done()


def main():
    port = find_arduino_port()
    if not port:
        raise FileNotFoundError("Could not find Arduino port.")

    print(f"Connected to Arduino at port: {port}")

    value = 0
    route_caller_thread = threading.Thread(target=route_caller, daemon=True)
    route_caller_thread.start()

    with serial.Serial(port, baudrate=9600) as ser:
        try:
            while True:
                line = ser.readline().decode("utf-8").strip()
                if line == MARKER:
                    value += 1
                    CALL_QUEUE.put(value)
                else:
                    print(f"Line does not match marker: {line}")
        except KeyboardInterrupt:
            print("Exiting; please wait for all calls to finish...")
            CALL_QUEUE.join()
