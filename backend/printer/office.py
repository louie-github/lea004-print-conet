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
    word = comtypes.client.CreateObject("Word.Application")
    word.Visible = False
    word.ActivePrinter = "Microsoft Print to PDF"
    document = word.Documents.Open(filename)
    document.PrintOut(
        PrintToFile=True,
        OutputFileName=str(Path(output_filename).resolve()),
        Append=False,
    )
    document.Close()
    # Keep Word open so subsequent calls aren't as slow.
    # word.Quit()
    return output_filename


def convert_excel(filename: str, output_filename: str):
    excel = comtypes.client.CreateObject("Excel.Application")
    excel.Visible = False
    workbook = excel.Workbooks.Open(filename)
    workbook.PrintOut(
        PrintToFile=True,
        PrToFileName=str(Path(output_filename).resolve()),
        # For some reason, setting excel.ActivePrinter doesn't work, so
        # we just do it here.
        ActivePrinter="Microsoft Print to PDF",
    )
    workbook.Close()
    # Keep Excel open so subsequent calls aren't as slow.
    # excel.Quit()
    return output_filename


def convert_office(filename: str, output_filename: Optional[str] = None):
    basename, ext = os.path.splitext(filename)
    if output_filename is None:
        output_filename = basename + ".pdf"

    if ext in {".doc", ".docx"}:
        return convert_word(filename, output_filename)
    elif ext in {".xls", ".xlsx", ".csv"}:
        return convert_excel(filename, output_filename)
    else:
        raise NotImplementedError(f"Cannot handle filetype '{ext}'")

    return True
