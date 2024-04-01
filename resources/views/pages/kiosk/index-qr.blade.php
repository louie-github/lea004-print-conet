@extends('layouts.app-kiosk')

@section('content')
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

                            <iframe id="pdf-iframe" class="mb-5" src="{{ route('pdf.viewer', ['id' => $transaction->document_id]) }}" width="100%" height="620px"></iframe>

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
            <div class="modal fade" id="cancelTransactionModal" tabindex="-1" aria-labelledby="cancelTransactionModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelTransactionModalLabel">Confirm Cancellation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to cancel the transaction?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                        if (documentId != '{{ $transaction->document_id }}') {
                            const iframeElement = document.querySelector('#pdf-iframe');
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

        // Reload the page when the "Reload" link is clicked
        document.getElementById('refreshLink').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default link behavior (navigation)
            location.reload(); // Reload the page
        });


        //for modal
        
        document.getElementById('confirmCancelBtn').addEventListener('click', function(event) {
            event.preventDefault();
         
        });

        // Open the modal when "Yes" is clicked
        document.getElementById('openCancelModal').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default link behavior
            // Open the modal programmatically
            var myModal = new bootstrap.Modal(document.getElementById('cancelTransactionModal'), {
                keyboard: false
            });
            myModal.show();
        });

        // Handle the confirmation of cancellation
        document.getElementById('confirmCancelBtn').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default button behavior
            // Implement your cancellation logic here, such as displaying a confirmation message
            console.log('Transaction cancelled.'); // Placeholder for actual cancellation logic

            // Make a POST request to the API endpoint for cancelling the transaction
            fetch('/kiosk/cancelled', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // Add any other headers or authentication tokens as needed
                },
                body: JSON.stringify({
                    transactionId: '{{ $transaction->id }}', // Include the transaction ID in the request body
                    cancellationReason: 'User cancelled the transaction' // Optional: Add a reason for cancellation
                })
            })
            .then(response => {
                if (response.ok) {
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
           console.log('Button clicked.'); // Test message to check if the button click event is detected
            var myModal = bootstrap.Modal.getInstance(document.getElementById('cancelTransactionModal'));
            myModal.hide();
        });

    });



    </script>
@endsection
