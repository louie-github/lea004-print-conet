#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from typing import Dict, Union

PRINTER_STATUS_BUSY = 512
PRINTER_STATUS_DOOR_OPEN = 4194304
PRINTER_STATUS_ERROR = 2
PRINTER_STATUS_INITIALIZING = 32768
PRINTER_STATUS_IO_ACTIVE = 256
PRINTER_STATUS_MANUAL_FEED = 32
PRINTER_STATUS_NO_TONER = 262144
PRINTER_STATUS_NOT_AVAILABLE = 4096
PRINTER_STATUS_OFFLINE = 128
PRINTER_STATUS_OUT_OF_MEMORY = 2097152
PRINTER_STATUS_OUTPUT_BIN_FULL = 2048
PRINTER_STATUS_PAGE_PUNT = 524288
PRINTER_STATUS_PAPER_JAM = 8
PRINTER_STATUS_PAPER_OUT = 16
PRINTER_STATUS_PAPER_PROBLEM = 64
PRINTER_STATUS_PAUSED = 1
PRINTER_STATUS_PENDING_DELETION = 4
PRINTER_STATUS_POWER_SAVE = 16777216
PRINTER_STATUS_PRINTING = 1024
PRINTER_STATUS_PROCESSING = 16384
PRINTER_STATUS_SERVER_UNKNOWN = 8388608
PRINTER_STATUS_TONER_LOW = 131072
PRINTER_STATUS_USER_INTERVENTION = 1048576
PRINTER_STATUS_WAITING = 8192
PRINTER_STATUS_WARMING_UP = 65536

PRINTER_ENUM_LOCAL = 2

PRINTER_ATTRIBUTE_WORK_OFFLINE = 1024


class LinuxPrinterHandle:
    def __init__(self, printer_name) -> None:
        self.printer_name = printer_name
        self.opened = False

    def __enter__(self):
        self.opened = True

    def __exit__(self, exc_type, exc_value, exc_traceback):
        self.opened = False


def OpenPrinter(printer_name: str):
    return LinuxPrinterHandle(printer_name)


def ClosePrinter(printer_handle: LinuxPrinterHandle):
    return True


def GetDefaultPrinter():
    return "Placeholder Printer 1"


def EnumPrinters(*args, **kwargs):
    return [
        {
            "pPrinterName": "Placeholder Printer 1",
            "pPortName": "PLACEH1:",
            "pDriverName": "Linux placeholder printer",
            "Attributes": 0,
            "Status": 0,
            "cJobs": 0,
        },
        {
            "pPrinterName": "Placeholder Printer 2",
            "pPortName": "PLACEH2:",
            "pDriverName": "Linux placeholder printer",
            "Attributes": PRINTER_ATTRIBUTE_WORK_OFFLINE,
            "Status": PRINTER_STATUS_OFFLINE,
            "cJobs": 0,
        },
    ]


def GetPrinter(printer_handle: LinuxPrinterHandle, *args, **kwargs) -> Dict[str, Union[str, int]]:
    for printer in EnumPrinters():
        if printer['pPrinterName'] == printer_handle.printer_name:
            return printer
    else:
        raise NotImplementedError