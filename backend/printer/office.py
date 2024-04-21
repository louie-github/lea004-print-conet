#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
from pathlib import Path
from typing import Optional

# For some unknown, infuriating reason, using keyword arguments simply
# DOES NOT WORK when using win32com. Hence, we use comtypes.client, even
# if it is a bit slower.
# TODO: Check if we should switch back to win32com and just suck up
# using positional arguments.
import comtypes.client


def convert_word(filename: str, output_filename: str):
    output_path = Path(output_filename).resolve()
    word = comtypes.client.CreateObject("Word.Application")
    word.Visible = False
    word.ActivePrinter = "Microsoft Print to PDF"
    document = word.Documents.Open(filename)
    document.PrintOut(PrintToFile=True, OutputFileName=str(output_path), Append=False)
    document.Close()
    # Keep Word open for faster calls.
    # word.Quit()


def convert_excel(filename: str, output_filename: str):
    output_path = Path(output_filename).resolve()
    excel = comtypes.client.CreateObject("Excel.Application")
    excel.Visible = False
    print(f"Active printer: {excel.ActivePrinter}")
    workbook = excel.Workbooks.Open(filename)
    workbook.PrintOut(
        PrintToFile=True,
        PrToFileName=str(output_path),
        # For some reason, setting excel.ActivePrinter doesn't work, so
        # we just do it here.
        ActivePrinter="Microsoft Print to PDF",
    )
    workbook.Close()
    # Keep Excel open for faster calls.
    # excel.Quit()


def convert_office(filename: str, output_filename: Optional[str] = None):
    basename, ext = os.path.splitext(filename)
    if output_filename is None:
        output_filename = basename + ".pdf"
    if ext in {".doc", ".docx"}:
        convert_word(filename, output_filename)
    elif ext in {".xls", ".xlsx", ".csv"}:
        convert_excel(filename, output_filename)
    else:
        raise NotImplementedError(f"Cannot handle filetype '{ext}'")

    return True
