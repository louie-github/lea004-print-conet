#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
from typing import Optional

import win32com.client

wdFormatPDF = 17
xlTypePDF = 0


def change_ext(filename: str, ext: str):
    if not ext.startswith("."):
        ext = "." + ext
    return os.path.splitext(filename)[0] + ext


def convert_word(filename: str, output_filename: Optional[str] = None):
    if output_filename is None:
        output_filename = change_ext(filename, "pdf")

    # TODO: Print to PDF instead of Save As. Saving will break when the
    # docx file has OpenType fonts, but should otherwise be okay.
    word = win32com.client.Dispatch("Word.Application")
    # word.Visible = False
    doc = word.Documents.Open(filename)
    doc.SaveAs2(FileName=output_filename, FileFormat=wdFormatPDF)
    doc.Close()
    # Keep Word open so subsequent calls aren't as slow
    # word.Quit()

    return output_filename


def convert_excel(filename: str, output_filename: Optional[str] = None):
    if output_filename is None:
        output_filename = change_ext(filename, "pdf")

    # TODO: Print to PDF instead of exporting. Exporting will break when
    # the xlsx file has OpenType fonts, but should otherwise be okay.
    excel = win32com.client.Dispatch("Excel.Application")
    # excel.Visible = False
    workbook = excel.Workbooks.Open(filename)
    workbook.ExportAsFixedFormat(xlTypePDF, output_filename)
    workbook.Close()
    # Keep Excel open so subsequent calls aren't as slow
    # excel.Quit()

    return output_filename
