@extends('layouts.app-kiosk')
@section('content')
<main class="main-content mt-0" id="mainContent">
    <section id="payment_details_kiosk">
        <div class="page-header min-vh-100">
            <div class="container">
                <div class="row">
                    <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                        <div class="card card-plain">
                            <div class="card-header pb-0 text-start">
                                <h4 class="font-weight-bolder">Payment details</h4>
                                <p class="mb-0">
                                    Note that this kiosk <b>does not dispense change</b>.
                                    Any excess payments will not be returned to you.
                                </p>
                            </div>
                            <div class="card-body">
                                <form role="form" id="paymentForm" method="POST" action="{{ route('kiosk.print') }}">
                                    @csrf
                                    <input type="hidden" name="transactionId"
                                           value="{{ $transaction->id }}">
                                    <div id="transactionDetails" class="mb-1">
                                        Transaction ID: {{ $transaction->id }}
                                    </div>
                                    <div id="transDescription" class="mb-1">
                                        @php
                                            $colorType = $transaction->is_colored
                                                ? "Colored" : "Black and White";
                                        @endphp
                                        Description: {{ $transaction->total_pages }} 
                                                     page/s ({{ $colorType }})
                                    </div>
                                    <div id="noCopies" class="mb-4">
                                        Copies: {{ $transaction->no_copies }}
                                    </div>
                                    <div class="mb-3">
                                        <h5 class="text-center">AMOUNT TO PAY:</h5>
                                        <h3 class="text-center" id="amountToPay">
                                            ₱{{ $transaction->amount_to_be_paid }}.00
                                        </h3>
                                    </div>
                                    <div class="mb-3">
                                        <h5 class="text-center">TOTAL COLLECTED:</h5>
                                        <h3 class="text-center" id="amountCollected">
                                            ₱{{ $transaction->amount_collected }}.00
                                        </h3>
                                    </div>
                                    <div class="text-center">
                                        <button type="button" id="printBtn"
                                            class="btn btn-lg btn-default btn-lg w-100 mt-4 mb-0 h5"
                                            disabled>
                                            Print
                                        </button>
                                    </div>
                                    <div class="text-center">
                                        <button type="button" id="printBtn"
                                            class="btn bg-gradient-secondary w-100 mt-4 mb-0"
                                            onclick="addAmount();">
                                            Simulate insert coin (₱1)
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="card-footer text-center pt-0 px-lg-2 px-1">
                                <p class="mb-4 text-sm mx-auto">
                                    Would you like to cancel the transaction?
                                    <a href="#" class="text-primary text-gradient font-weight-bold"
                                        id="openCancelModal" data-bs-toggle="modal"
                                        data-bs-target="#cancelTransactionModal">Yes</a>
                                </p>
                            </div>

                        </div>
                    </div>
                    <div
                        class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">

                        @if ($transaction)
                            <iframe id="pdf-iframe" class="mb-5"
                                src="{{ route('pdf.viewer', ['id' => $transaction->document_id]) }}" width="100%"
                                height="620px"></iframe>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal for confirming transaction cancellation -->
    <div class="modal fade" id="cancelTransactionModal" tabindex="-1" aria-labelledby="cancelTransactionModalLabel"
        data-dismiss="modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelTransactionModalLabel">Confirm Cancellation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to cancel the transaction?
                </div>
                <meta name="csrf-token" content="{{ csrf_token() }}">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="confirmCancelBtn">Yes, Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Print status modal -->
    @if (session()->has('succes') || session()->has('error'))
    <div class="modal fade" id="printStatusModal" tabindex="-1" aria-labelledby="printStatusModal"
        data-dismiss="modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printStatusModalLabel">Print Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @if ($message = session()->get('succes'))
                <div class="modal-body">
                    <h4 class="text-center">Success!</h4>
                    <p class="text-center">Your print job has been sent.</p>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-primary" href="/kiosk/qr">Finish</a>
                </div>
                @elseif ($message = session()->get('error'))
                <div class="modal-body">
                    <h4 class="text-center">Error!</h4>
                    <p class="text-center">
                        The print job failed. Message from the server:<br>
                        {{ $message }}
                    </p>
                    <h4 class="text-center">Please try again.</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                            href="/kiosk/qr">Close</a>
                </div>
                @endif
            </div>
        </div>
    </div>
    <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                (new bootstrap.Modal('#printStatusModal')).show();
            });
    </script>
    @endif

</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.paymentCheckIntervalId = setInterval(function() {
            fetch("{{ route('transaction.show', ['transaction' => $transaction]) }}")
                .then(response => response.json())
                .then(data => {
                    const amountCollected = '₱' + data.transaction.amount_collected + ".00";
                    document.getElementById('amountCollected').innerText = amountCollected;
                    if (data.transaction.amount_collected >= data.transaction.amount_to_be_paid) {
                        reachedTotal = true;
                        const printBtn = document.getElementById("printBtn");
                        printBtn.classList.remove("btn-default");
                        printBtn.classList.add("btn-primary");
                        printBtn.disabled = false;
                    }
                })
                .catch(error => console.error('Error:', error));
        }, 500);
    });

    function addAmount() {
        fetch("{{ route('pulsePayment', ['transaction' => $transaction]) }}", {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                pulseValue: 1,
            })
        });
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let paymentDetailsKiosk = document.getElementById('payment_details_kiosk');
        let messageElement = document.getElementById('error-message');

        // Open the modal when "Yes" is clicked
        document.getElementById('openCancelModal').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default link behavior
            // Open the modal programmatically
            var myModal = new bootstrap.Modal(document.getElementById('cancelTransactionModal'), {
                keyboard: false
            });

            myModal.show();
        });

        //cancel process modal
        document.getElementById('confirmCancelBtn').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default button behavior
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            // Get the transaction ID from the HTML element or a variable
            const transactionId = '{{ $transaction->id ?? 'default_transaction_id' }}';

            // Create an object with the data to send in the request
            const data = {
                _token: csrfToken,
                transactionId: transactionId,
                cancellationReason: 'User cancelled the transaction' // Optional: Add a reason for cancellation
            };

            // Make a POST request using the fetch API
            fetch('/kiosk/cancelled', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // Add any other headers or authentication tokens as needed
                    },
                    body: JSON.stringify(data) // Convert the data object to JSON format
                })
                .then(response => {
                    if (response.ok) {
                        location.reload();
                        return;
                        console.log('Transaction cancellation request sent successfully.');
                        // Optionally, display a success message or perform any additional actions
                    } else {
                        console.error('Error sending cancellation request:', response.status);
                        // Handle error response from the API if needed
                    }
                })
                .catch(error => {
                    console.error('Error sending cancellation request:', error);
                    // Handle network or other errors if the request fails
                });

            // Close the modal after cancellation
            var myModal = bootstrap.Modal.getInstance(document.getElementById(
            'cancelTransactionModal'));
            myModal.hide();
        });


        // submit print job
        const printBtn = document.getElementById('printBtn');
        const paymentForm = document.getElementById('paymentForm');

        printBtn.addEventListener('click', function() {
            clearInterval(paymentCheckIntervalId);

            // TODO: Validate data
            // If validations pass, submit the form

            // NOTE: Delay for one second to let all payment checking 
            // requests go through.
            // This is janky, but we need it to get sessions to work.
            setTimeout(() => {paymentForm.submit();}, 1000);
        });
    });
</script>
@endsection
