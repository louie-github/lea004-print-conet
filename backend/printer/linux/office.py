#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import shutil
import subprocess
import tempfile
from pathlib import Path
from typing import Optional

CONVERT_COMMAND = [
    "libreoffice",
    "--headless",
    "--convert-to",
    "pdf",
]


def find_pdf_file(dirname: str):
    for fname in os.listdir(dirname):
        if fname.endswith(".pdf"):
            return fname
    else:
        raise FileNotFoundError


def _convert_office(filename: str, output_filename: str):
    with tempfile.TemporaryDirectory() as tempdir:
        subprocess.run(
            ["libreoffice", "--headless", "--convert-to", "pdf"]
            + ["--outdir", tempdir]
            + [filename]
        )
        shutil.copyfile(os.path.join(tempdir, find_pdf_file(tempdir)), output_filename)
    return output_filename


convert_word = _convert_office
convert_excel = _convert_office


def convert_office(filename: str, output_filename: Optional[str] = None):
    basename, ext = os.path.splitext(filename)
    if output_filename is None:
        output_filename = basename + ".pdf"

    filename = os.path.abspath(filename)
    output_filename = os.path.abspath(output_filename)

    if ext in {".doc", ".docx"}:
        return convert_word(filename, output_filename)
    elif ext in {".xls", ".xlsx", ".csv"}:
        return convert_excel(filename, output_filename)
    else:
        raise NotImplementedError(f"Cannot handle filetype '{ext}'")
