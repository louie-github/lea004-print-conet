#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import base64
import logging
import os
import sys
import tempfile
import time
from pathlib import Path
from typing import Optional

import uvicorn
from fastapi import FastAPI, HTTPException
from fastapi.encoders import jsonable_encoder
from pydantic import BaseModel, Field

if sys.platform == "win32":
    from .office import convert_office
else:
    from .linux.office import convert_office
from .printing import (
    get_default_printer,
    get_printer_status,
    get_printers,
    print_file,
)

# API portion
app = FastAPI()


class PrintConfiguration(BaseModel):
    printer_name: Optional[str] = Field(default=None)


class PrintJob(BaseModel):
    file_data: str
    has_color: bool = Field(default=True)
    page_start: int = Field(default=0)
    page_end: int = Field(default=0)
    num_copies: int = Field(default=1)


class FileConvertJob(BaseModel):
    data: str
    extension: str


@app.get("/platform")
async def get_platform():
    if sys.platform == "win32":
        office_converter = "Microsoft Office"
    else:
        office_converter = "LibreOffice"
    return {
        "platform": sys.platform,
        "office_converter": office_converter,
    }


@app.get("/status")
async def read_status(printer_name: Optional[str] = None):
    try:
        if printer_name is None:
            printer_name = app.state.printer_name
        return {
            "message": "Successfully read printer status.",
            "printer_name": printer_name,
            "status": get_printer_status(printer_name),
        }
    except FileNotFoundError:
        raise HTTPException(
            400,
            {"message": "Printer could not be found.", "printer_name": printer_name},
        )
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


@app.get("/list_printers")
async def list_printers():
    return {"printers": get_printers(), "selected_printer": app.state.printer_name}


@app.post("/configure")
async def select_printer(config: PrintConfiguration):
    try:
        if config.printer_name is None:
            app.state.printer_name = get_default_printer()
        else:
            app.state.printer_name = config.printer_name
        return {
            "message": "Printer configuration set successfully.",
            "status": get_printer_status(app.state.printer_name),
        }
    except FileNotFoundError:
        raise HTTPException(
            400,
            {
                "message": "Printer could not be found.",
                "config": jsonable_encoder(config),
            },
        )
    except Exception as e:
        raise HTTPException(
            500,
            {
                "message": "An error occurred while configuring printer settings.",
                "exception": e,
                "config": jsonable_encoder(config),
            },
        )


def check_file_locked(filename: str):
    if sys.platform != "win32":
        WindowsError = IOError

    # From: https://blogs.blumetech.com/blumetechs-tech-blog/2011/05/python-file-locking-in-windows.html
    # Found via StackOverflow: https://stackoverflow.com/a/63761161
    try:
        f = open(filename, "rb")
        f.close()
    except IOError:
        return True

    lock_file = filename + ".pylck"
    if os.path.exists(lock_file):
        os.remove(lock_file)
    try:
        os.rename(filename, lock_file)
        time.sleep(1)
        os.rename(lock_file, filename)
    except WindowsError:
        return True

    return False


@app.post("/convert")
async def convert_office_file(job: FileConvertJob):
    with tempfile.TemporaryDirectory() as tempdir:
        input_path = os.path.join(tempdir, f"input.{job.extension}")
        with open(input_path, "wb") as input_file:
            input_file.write(base64.b64decode(job.data))
        output_path = os.path.join(tempdir, "output.pdf")

        try:
            convert_office(input_path, output_path)
        except NotImplementedError:
            raise HTTPException(
                415,
                {
                    "message": "Only .doc(x), .xls(x), and .csv files are accepted.",
                    "job": jsonable_encoder(job),
                },
            )
        except Exception as e:
            raise HTTPException(
                500,
                {
                    "message": "An unknown error occurred while converting.",
                    "exception": repr(e),
                    "job": jsonable_encoder(job),
                },
            )
        else:
            while check_file_locked(output_path):
                time.sleep(0.5)
            with open(output_path, "rb") as output_file:
                base64_data = base64.b64encode(output_file.read())
            return {
                "message": "Conversion was successful",
                "data": base64_data,
                "original_extension": job.extension,
            }


@app.post("/print")
async def queue_print_job(job: PrintJob):
    printer_status = get_printer_status(app.state.printer_name)["status"]
    if "OFFLINE" in printer_status or "ERROR" in printer_status:
        raise HTTPException(
            503,
            {
                "message": "Printer is currently offline or in an ERROR state.",
                "job": jsonable_encoder(job),
            },
        )
    # Can't use context manager here. We can't open the file if Python
    # also has it open, so we need to manually delete it after printing.
    tempf = tempfile.NamedTemporaryFile("wb", suffix=".pdf", delete=False)
    tempf.write(base64.b64decode(job.file_data))
    tempf.close()
    try:
        print_file(
            app.state.printer_name,
            tempf.name,
            has_color=job.has_color,
            page_start=job.page_start,
            page_end=job.page_end,
            num_copies=job.num_copies,
        )
    except FileNotFoundError:
        raise HTTPException(
            404, {"message": "File could not be found.", "job": jsonable_encoder(job)}
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
    finally:
        os.remove(tempf.name)


def log_printer_status(printer_name):
    printer_status = get_printer_status(printer_name)
    logging.info("Printer status:")
    logging.info(f'  - Name: {printer_status["name"]}')
    logging.info(f'  - Port: {printer_status["port"]}')
    logging.info(f'  - Driver: {printer_status["driver"]}')
    logging.info(f'  - Current status: {printer_status["status"]}')
    logging.info(f'  - Number of jobs: {printer_status["jobs"]}')


def cli_main(printer_name):
    log_printer_status(printer_name)


def main(args=__import__("sys").argv[1:]):
    printer_name = None
    run_api = "api" in args

    logging.basicConfig(format="[%(levelname)s] %(message)s", level=logging.INFO)

    if not printer_name:
        logging.info("No printer specified; using default printer.")
        printer_name = get_default_printer()

    app.state.printer_name = printer_name
    cli_main(app.state.printer_name)
    if run_api:
        uvicorn.run(app, host="0.0.0.0", port=48250)


if __name__ == "__main__":
    main()
