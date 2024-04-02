#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import pypdf


def is_readable_pdf(filename: str):
    try:
        pypdf.PdfReader(filename)
    except Exception:
        return False
    else:
        return True


def get_num_pages(filename: str):
    return len(pypdf.PdfReader(filename).pages)
