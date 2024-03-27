#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from pathlib import Path

import win32api
import win32print

PRINTER_STATUS_CODES = {
    win32print.PRINTER_STATUS_BUSY: "PRINTER_STATUS_BUSY",
    win32print.PRINTER_STATUS_DOOR_OPEN: "PRINTER_STATUS_DOOR_OPEN",
    win32print.PRINTER_STATUS_ERROR: "PRINTER_STATUS_ERROR",
    win32print.PRINTER_STATUS_INITIALIZING: "PRINTER_STATUS_INITIALIZING",
    win32print.PRINTER_STATUS_IO_ACTIVE: "PRINTER_STATUS_IO_ACTIVE",
    win32print.PRINTER_STATUS_MANUAL_FEED: "PRINTER_STATUS_MANUAL_FEED",
    win32print.PRINTER_STATUS_NO_TONER: "PRINTER_STATUS_NO_TONER",
    win32print.PRINTER_STATUS_NOT_AVAILABLE: "PRINTER_STATUS_NOT_AVAILABLE",
    win32print.PRINTER_STATUS_OFFLINE: "PRINTER_STATUS_OFFLINE",
    win32print.PRINTER_STATUS_OUT_OF_MEMORY: "PRINTER_STATUS_OUT_OF_MEMORY",
    win32print.PRINTER_STATUS_OUTPUT_BIN_FULL: "PRINTER_STATUS_OUTPUT_BIN_FULL",
    win32print.PRINTER_STATUS_PAGE_PUNT: "PRINTER_STATUS_PAGE_PUNT",
    win32print.PRINTER_STATUS_PAPER_JAM: "PRINTER_STATUS_PAPER_JAM",
    win32print.PRINTER_STATUS_PAPER_OUT: "PRINTER_STATUS_PAPER_OUT",
    win32print.PRINTER_STATUS_PAPER_PROBLEM: "PRINTER_STATUS_PAPER_PROBLEM",
    win32print.PRINTER_STATUS_PAUSED: "PRINTER_STATUS_PAUSED",
    win32print.PRINTER_STATUS_PENDING_DELETION: "PRINTER_STATUS_PENDING_DELETION",
    win32print.PRINTER_STATUS_POWER_SAVE: "PRINTER_STATUS_POWER_SAVE",
    win32print.PRINTER_STATUS_PRINTING: "PRINTER_STATUS_PRINTING",
    win32print.PRINTER_STATUS_PROCESSING: "PRINTER_STATUS_PROCESSING",
    win32print.PRINTER_STATUS_SERVER_UNKNOWN: "PRINTER_STATUS_SERVER_UNKNOWN",
    win32print.PRINTER_STATUS_TONER_LOW: "PRINTER_STATUS_TONER_LOW",
    win32print.PRINTER_STATUS_USER_INTERVENTION: "PRINTER_STATUS_USER_INTERVENTION",
    win32print.PRINTER_STATUS_WAITING: "PRINTER_STATUS_WAITING",
    win32print.PRINTER_STATUS_WARMING_UP: "PRINTER_STATUS_WARMING_UP",
}


def _read_printer_status(status: int):
    for key, value in PRINTER_STATUS_CODES.items():
        if status & key:
            yield value


def read_printer_status(status: int):
    return list(_read_printer_status(status))


class PrinterHandle:
    def __init__(self, printer: str):
        self.printer = printer
        self.handle = None

    def __enter__(self):
        self.handle = win32print.OpenPrinter(self.printer)
        return self.handle

    def __exit__(self, exc_type, exc_value, exc_traceback):
        if self.handle is not None:
            win32print.ClosePrinter(self.handle)


def get_default_printer():
    return win32print.GetDefaultPrinter()


def get_printer_status(printer_handle):
    info_dict = win32print.GetPrinter(printer_handle, 2)
    return {
        "name": info_dict.get("pPrinterName"),
        "port": info_dict.get("pPortName"),
        "driver": info_dict.get("pDriverName"),
        "status": read_printer_status(info_dict.get("Status")),
        "raw_status": info_dict.get("Status"),
        "jobs": info_dict.get("cJobs"),
    }


def print_file(printer_handle, filename):
    printer_name = get_printer_status(printer_handle)["name"]
    # TODO: Verify that filename is supported (PDF)
    # TODO: Verify that file is actually PDF and printable
    fpath = Path(filename).resolve()
    if not fpath.exists():
        raise FileNotFoundError(f"Could not find file: {fpath}")
    win32api.ShellExecute(0, "print", str(fpath), f'/d:"{printer_name}"', ".", 0)
