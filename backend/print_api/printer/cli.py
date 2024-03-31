#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import logging
from typing import Dict, Tuple

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
    total_payment: int
    has_color: bool = Field(default=True)
    page_start: int = Field(default=0)
    page_end: int = Field(default=0)
    num_copies: int = Field(default=1)


CURRENT_JOBS: Dict[str, Tuple[PrintJob, int]] = {}


@app.get("/status/")
async def read_status():
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


def _check_payment_status(transaction_id: str):
    # TODO: Implement real payment checking.
    return {
        "total_payment": CURRENT_JOBS[transaction_id][0].total_payment,
        "amount_collected": CURRENT_JOBS[transaction_id][0].total_payment,
        "amount_needed": 0,
    }


@app.get("/payment/{transaction_id}")
async def check_payment_status(transaction_id: str):
    return _check_payment_status(transaction_id)


@app.post("/print/")
async def queue_print_job(job: PrintJob):
    if job.transaction_id not in CURRENT_JOBS:
        CURRENT_JOBS[job.transaction_id] = (job, 0)
        return {
            "message": "Print job has been queued. Waiting for payment.",
            "job": jsonable_encoder(job),
        }

    payment_status = _check_payment_status(job.transaction_id)
    if payment_status["amount_needed"] != 0:
        return {
            "message": "Print job is currently queued and awaiting complete payment.",
            "job": jsonable_encoder(job),
            "payment_status": jsonable_encoder(payment_status),
        }

    try:
        print_file(
            app.state.printer_handle,
            job.filename,
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
        CURRENT_JOBS.pop(job.transaction_id)
        return {
            "message": "Payment complete. Print job sent.",
            "job": jsonable_encoder(job),
        }


def log_printer_status(printer_handle):
    printer_status = get_printer_status(printer_handle)
    logging.info("Printer status:")
    logging.info(f"  - Name: {printer_status["name"]}")
    logging.info(f"  - Port: {printer_status["port"]}")
    logging.info(f"  - Driver: {printer_status["driver"]}")
    logging.info(f"  - Current status: {printer_status["status"]}")
    logging.info(f"  - Number of jobs: {printer_status["jobs"]}")


def cli_main(printer_handle):
    log_printer_status(printer_handle)


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
