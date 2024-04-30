#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import logging
import shlex
import subprocess
import sys
from pathlib import Path
from typing import Dict, List

if sys.platform == "win32":
    import win32print
else:
    from .linux import win32print

from .pdf import get_num_pages, is_readable_pdf

PRINTER_STATUS_CODES = {
    win32print.PRINTER_STATUS_BUSY: "BUSY",
    win32print.PRINTER_STATUS_DOOR_OPEN: "DOOR_OPEN",
    win32print.PRINTER_STATUS_ERROR: "ERROR",
    win32print.PRINTER_STATUS_INITIALIZING: "INITIALIZING",
    win32print.PRINTER_STATUS_IO_ACTIVE: "IO_ACTIVE",
    win32print.PRINTER_STATUS_MANUAL_FEED: "MANUAL_FEED",
    win32print.PRINTER_STATUS_NO_TONER: "NO_TONER",
    win32print.PRINTER_STATUS_NOT_AVAILABLE: "NOT_AVAILABLE",
    win32print.PRINTER_STATUS_OFFLINE: "OFFLINE",
    win32print.PRINTER_STATUS_OUT_OF_MEMORY: "OUT_OF_MEMORY",
    win32print.PRINTER_STATUS_OUTPUT_BIN_FULL: "OUTPUT_BIN_FULL",
    win32print.PRINTER_STATUS_PAGE_PUNT: "PAGE_PUNT",
    win32print.PRINTER_STATUS_PAPER_JAM: "PAPER_JAM",
    win32print.PRINTER_STATUS_PAPER_OUT: "PAPER_OUT",
    win32print.PRINTER_STATUS_PAPER_PROBLEM: "PAPER_PROBLEM",
    win32print.PRINTER_STATUS_PAUSED: "PAUSED",
    win32print.PRINTER_STATUS_PENDING_DELETION: "PENDING_DELETION",
    win32print.PRINTER_STATUS_POWER_SAVE: "POWER_SAVE",
    win32print.PRINTER_STATUS_PRINTING: "PRINTING",
    win32print.PRINTER_STATUS_PROCESSING: "PROCESSING",
    win32print.PRINTER_STATUS_SERVER_UNKNOWN: "SERVER_UNKNOWN",
    win32print.PRINTER_STATUS_TONER_LOW: "TONER_LOW",
    win32print.PRINTER_STATUS_USER_INTERVENTION: "USER_INTERVENTION",
    win32print.PRINTER_STATUS_WAITING: "WAITING",
    win32print.PRINTER_STATUS_WARMING_UP: "WARMING_UP",
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

    def close(self):
        if self.handle is not None:
            win32print.ClosePrinter(self.handle)

    def __exit__(self, exc_type, exc_value, exc_traceback):
        self.close()


def get_default_printer():
    return win32print.GetDefaultPrinter()


def get_printers() -> List[str]:
    return [
        printer_info["pPrinterName"]
        for printer_info in win32print.EnumPrinters(
            win32print.PRINTER_ENUM_LOCAL, None, 2
        )
    ]


def get_printer_status(printer_name: str) -> Dict[str, str | list[str]]:
    # TODO: Make this a try-except instead of if-else.
    if printer_name not in get_printers():
        raise FileNotFoundError(f"Could not find printer with name: {printer_name}")

    with PrinterHandle(printer_name) as printer_handle:
        info_dict = win32print.GetPrinter(printer_handle, 2)

    # Windows, for some reason, has two ways to check if a printer is
    # offline. We have to handle both.
    # https://stackoverflow.com/q/41437023
    printer_status_list = read_printer_status(info_dict.get("Status"))
    if (
        info_dict.get("Attributes") & win32print.PRINTER_ATTRIBUTE_WORK_OFFLINE
    ) and "OFFLINE" not in printer_status_list:
        printer_status_list.append("OFFLINE")

    return {
        "name": info_dict.get("pPrinterName"),
        "port": info_dict.get("pPortName"),
        "driver": info_dict.get("pDriverName"),
        "status": printer_status_list,
        "raw_status": info_dict.get("Status"),
        "jobs": info_dict.get("cJobs"),
    }


def generate_print_command(
    filename: str,
    printer_name=None,
    has_color: bool = True,
    num_copies: int = 1,
    page_start: int = 0,
    page_end: int = 0,
):
    command = ["sumatrapdf.exe"]
    print_settings = []

    if printer_name is None:
        command.append("-print-to-default")
    else:
        command.extend(["-print-to", printer_name])

    if page_start == 0 and page_end == 0:
        pass
    else:
        if page_start == 0:
            page_start = 1
        if page_end == 0:
            page_end = get_num_pages(filename)
        print_settings.append(f"{page_start}-{page_end}")

    print_settings.append("fit")  # TODO: Add config for page fit
    print_settings.append(f"{num_copies}x")
    print_settings.append("color" if has_color else "monochrome")

    command.append("-print-settings")
    command.append(",".join(print_settings))
    command.append(filename)
    return command


def print_file(
    printer_name,
    filename: str,
    has_color: bool = True,
    num_copies: int = 1,
    page_start: int = 0,
    page_end: int = 0,
):
    if sys.platform != "win32":
        return True

    fpath = Path(filename).resolve()
    if not fpath.exists():
        raise FileNotFoundError(f"Could not find file: {fpath}")
    if not is_readable_pdf(filename):
        raise ValueError(f"Invalid PDF file: {fpath}")
    command = generate_print_command(
        str(fpath),
        printer_name=printer_name,
        has_color=has_color,
        num_copies=num_copies,
        page_start=page_start,
        page_end=page_end,
    )
    logging.info(f"Running PRINT command: {shlex.join(command)}")
    return subprocess.run(command)
