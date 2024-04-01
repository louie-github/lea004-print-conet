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
</style>

@section('content')
<div class="loading-container" id="loadingContainer">
    <div class="loading-spinner"></div>
</div>
    <main class="main-content mt-0">
        <section id="payment_details_kiosk">
            <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                            <div class="card card-plain">
                                <div class="card-header pb-0 text-start">
                                    <h4 class="font-weight-bolder">Print Details</h4>
                                    <p class="mb-0">Please verify the document before proceeding with the <b>payment.</b></p>
                                </div>
                                <div class="card-body">
                                    <form role="form">
                                        <div id="transactionDetails" class="mb-3"></div>
                                        <div id="transDescription" class="mb-3"></div>
                                        <div id="noCopies" class="mb-3"></div>
                                        <div id="totalAmount" class="mb-3"></div>
                                        <div class="text-center">
                                            <button type="button" class="btn btn-lg btn-primary btn-lg w-100 mt-4 mb-0">Pay now</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                                    <p class="mb-4 text-sm mx-auto">
                                        Would you like to cancel the transaction?
                                        <a href="#" class="text-primary text-gradient font-weight-bold" id="openCancelModal" data-bs-toggle="modal" data-bs-target="#cancelTransactionModal">Yes</a>
                                    </p>
                                </div>
                                
                            </div>
                        </div>
                        <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
                            @php
                                $transaction = App\Models\Transaction::latest()->first();
                            @endphp

                            @if ($transaction)
                                <iframe id="pdf-iframe" class="mb-5" src="{{ route('pdf.viewer', ['id' => $transaction->document_id]) }}" width="100%" height="620px"></iframe>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section id="qr_kiosk">
            <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                            <div class="card card-plain">
                                <div class="card-header pb-0 text-start">
                                    <h4 id="error-message"class="font-weight-bolder"></h4>
                                    {{-- <p class="mb-0">Seamless Access: Effortlessly Connected Through QR</p> --}}
                                </div>
                                <div class="card-body">
                                    @php
                                        $url = env('KIOSK_URL');
                                    @endphp
                                    {!! 
                                        QrCode::size(300)
                                            ->backgroundColor(255, 255, 255)
                                            ->color(153, 33, 3)
                                            ->margin(1)
                                            ->generate($url);
                                    !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
                            <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden">
                                <span class="mask bg-gradient-primary opacity-6"></span>
                                <h4 class="mt-5 text-white font-weight-bolder position-relative">Access the APP through the QR code now!</h4>
                                <p class="text-white position-relative">Seamless Access: Effortlessly Connected Through QR</p>
                                <img src="{{ asset('/img/shapes/arrow.svg') }}" alt="Arrow SVG">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modal for confirming transaction cancellation -->
            <div class="modal fade" id="cancelTransactionModal" tabindex="-1" aria-labelledby="cancelTransactionModalLabel" data-dismiss="modal"  aria-hidden="true">
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
        let transactionId;
        let qrKioskElement = document.getElementById('qr_kiosk');
        let paymentDetailsKiosk = document.getElementById('payment_details_kiosk');
        let messageElement = document.getElementById('error-message');

        // Function to load dynamic content via AJAX
        function loadDynamicContent() {
            fetch('{{ route('content.kiosk') }}')
                .then(response => response.json())
                .then(data => {
                    if (data.response === 200) {
                        const transDescription = data.transactions.total_pages + ' pages ' + (data.transactions.is_colored ? 'Colored' : 'B&W');
                        const noCopies = 'Copies: ' + data.transactions.no_copies;
                        const totalAmount = 'TOTAL: ₱' + data.transactions.amount_to_be_paid;

                        transactionId = data.transactions.id;
                        documentId = data.transactions.document_id
                        qrKioskElement.style.display = 'none';
                        paymentDetailsKiosk.style.display = 'block'; // Show transaction details section
                        document.getElementById('transactionDetails').innerText = 'Transaction ID: ' + transactionId;
                        document.getElementById('transDescription').innerText = 'Description: ' + transDescription;
                        document.getElementById('noCopies').innerText = noCopies;
                        document.getElementById('totalAmount').innerText = totalAmount;

                        // Update the iframe source only if the transaction ID changes
                        if (documentId != '{{ $transaction->document_id ?? 'default_document_id' }}') {
                            console.log('test',data);
                            const iframeElement = document.querySelector('#pdf-iframe');
                                if(!iframeElement){
                                    location.reload();
                                    return;
                                }
                            iframeElement.src = '{{ route('pdf.viewer', ['id' => 'TRANSACTION_DOCUMENT_ID']) }}'.replace('TRANSACTION_DOCUMENT_ID', data.transactions.document_id);
                            location.reload();
                            return;
                        }

                    } else {
                        qrKioskElement.style.display = 'block'; // Show QR section
                        paymentDetailsKiosk.style.display = 'none'; // Hide transaction details section
                        // messageElement.innerText = data.error;
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
            var myModal = bootstrap.Modal.getInstance(document.getElementById('cancelTransactionModal'));
            myModal.hide();
        });

        //simulate loading
        const loadingContainer = document.getElementById('loadingContainer');
        const content = document.getElementById('content');

        // Simulate loading time
        setTimeout(function () {
            loadingContainer.style.display = 'none';
            content.style.display = 'block';
        }, 1000); // Adjust the time as needed (in milliseconds)
    });



    </script>
@endsection
