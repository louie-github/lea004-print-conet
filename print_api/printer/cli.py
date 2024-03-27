#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import logging
from typing import Optional

import uvicorn
from fastapi import FastAPI, HTTPException
from fastapi.encoders import jsonable_encoder
from pydantic import BaseModel, Field

from .printing import (
    PrinterHandle,
    get_default_printer,
    get_printer_status,
    print_file,
)

# API portion
app = FastAPI()


class PrintJob(BaseModel):
    filename: str
    transaction_id: str
    has_color: Optional[bool] = Field(default=False)
    page_start: Optional[int] = Field(default=0)
    page_end: Optional[int] = Field(default=0)
    num_copies: Optional[int] = Field(default=1)


@app.get("/status/")
async def api_read_status():
    try:
        return get_printer_status(app.state.printer_handle)
    except AttributeError:
        raise HTTPException(
            404,
            {
                "message": (
                    "No printer is currently selected. "
                    "Please configure the server correctly."
                ),
            },
        )


@app.post("/print/")
async def api_print_file(job: PrintJob):
    try:
        print_file(app.state.printer_handle, job.filename)
    except FileNotFoundError:
        raise HTTPException(
            404,
            {
                "message": "File could not be found.",
                "job": jsonable_encoder(job),
            },
        )
    except Exception as e:
        raise HTTPException(
            500,
            {
                "message": "An unknown error occurred.",
                "exception": e,
                "job": jsonable_encoder(job),
            },
        )
    else:
        return {
            "message": "Print job sent.",
            "job": jsonable_encoder(job),
        }


def print_printer_status(printer_handle):
    printer_status = get_printer_status(printer_handle)
    logging.info("Printer status:")
    logging.info(f"  - Name: {printer_status["name"]}")
    logging.info(f"  - Port: {printer_status["port"]}")
    logging.info(f"  - Driver: {printer_status["driver"]}")
    logging.info(f"  - Current status: {printer_status["status"]}")
    logging.info(f"  - Number of jobs: {printer_status["jobs"]}")


def cli_main(printer_handle):
    print_printer_status(printer_handle)


def main(args=__import__("sys").argv[1:]):
    printer = None
    run_api = "api" in args

    logging.basicConfig(format="[%(levelname)s] %(message)s", level=logging.DEBUG)

    if not printer:
        logging.debug("No printer specified; using default printer.")
        printer = get_default_printer()

    with PrinterHandle(printer) as printer_handle:
        cli_main(printer_handle)
        app.state.printer_handle = printer_handle
        if run_api:
            uvicorn.run(app, host="127.0.0.1", port=48250)


if __name__ == "__main__":
    main()
