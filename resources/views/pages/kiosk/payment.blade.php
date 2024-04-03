@extends('layouts.app-kiosk')
<style>
    .loading-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .loading-spinner {
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-top: 4px solid #333;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .content {
        display: none;
    }
    /* Initially hide the main content */
    #main-content {
        display: none;
    }
    
</style>

@section('content')
<div class="loading-container" id="loadingContainer">
    <div class="loading-spinner"></div>
</div>
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
                                        Please insert the coins into the coin slot.
                                        Note that this kiosk <b>does not output change</b>.
                                        Any excess payments will be ignored.
                                    </p>
                                </div>
                                <div class="card-body">
                                    <form role="form" id="paymentForm" action="">
                                        <div id="transactionDetails" class="mb-1"></div>
                                        <div id="transDescription" class="mb-1"></div>
                                        <div id="noCopies" class="mb-4"></div>
                                        <div class="mb-3">
                                            <h5 class="text-center">AMOUNT TO PAY:</h5>
                                            <h3 class="text-center" id="amountToPay"></h3>
                                        </div>
                                        <div class="mb-3">
                                            <h5 class="text-center">TOTAL COLLECTED:</h5>
                                            <h3 class="text-center" id="amountCollected"></h3>
                                        </div>
                                        <div class="text-center">
                                            <button type="button" id="printBtn"
                                                class="btn btn-lg btn-default btn-lg w-100 mt-4 mb-0 h5"
                                                disabled>
                                                Print
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
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let paymentDetailsKiosk = document.getElementById('payment_details_kiosk');
            let messageElement = document.getElementById('error-message');

            // Function to load dynamic content via AJAX
            function loadDynamicContent() {
                fetch("{{ route('transaction.show', ['transaction' => $transaction]) }}")
                    .then(response => response.json())
                    .then(data => {
                        if (data.response === 200) {
                            const transDescription = data.transaction.total_pages + ' pages '
                            const is_colored = data.transaction.is_colored ? 'Colored' : 'BW';
                            const noCopies = 'Copies: ' + data.transaction.no_copies;
                            const amountToPay = '₱' + data.transaction.amount_to_be_paid + ".00";
                            const amountCollected = '₱' + data.transaction.amount_collected + ".00";

                            transactionId = data.transaction.id;
                            documentId = data.transaction.document_id
                            paymentDetailsKiosk.style.display = 'block'; // Show transaction details section
                            document.getElementById('transactionDetails').innerText = 'Transaction ID: ' +
                                transactionId;
                            document.getElementById('transDescription').innerText = 'Description: ' +
                                transDescription;
                            document.getElementById('noCopies').innerText = noCopies;
                            document.getElementById('amountToPay').innerText = amountToPay;
                            document.getElementById('amountCollected').innerText = amountCollected;

                            // Update the iframe source only if the transaction ID changes
                            if (documentId != '{{ $transaction->document_id ?? 'default_document_id' }}') {
                                console.log('test', data);
                                const iframeElement = document.querySelector('#pdf-iframe');
                                if (!iframeElement) {
                                    location.reload();
                                    return;
                                }
                                iframeElement.src =
                                    '{{ route('pdf.viewer', ['id' => 'TRANSACTION_DOCUMENT_ID']) }}'.replace(
                                        'TRANSACTION_DOCUMENT_ID', data.transactions.document_id);
                                location.reload();
                                return;
                            }

                        } else {
                            alert("No transaction found.");
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Call the function initially
            loadDynamicContent();

            // Refresh content every 5 seconds
            setInterval(loadDynamicContent, 5000);


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

            //simulate loading
            const loadingContainer = document.getElementById('loadingContainer');
            const mainContent = document.getElementById('mainContent');

            // Show loading animation
            loadingContainer.style.display = 'flex';

            // Simulate loading time
            setTimeout(function() {
                loadingContainer.style.display = 'none'; // Hide loading animation
                mainContent.style.display = 'block'; // Show main content
            }, 2000); // Adjust the time as needed (in milliseconds)


            // submit print job
            const printBtn = document.getElementById('printBtn');
            const paymentForm = document.getElementById('paymentForm');

            payNowBtn.addEventListener('click', function() {
                // TODO: Validate data

                // If validations pass, submit the form
                paymentForm.submit();
            });
        });
    </script>
@endsection
