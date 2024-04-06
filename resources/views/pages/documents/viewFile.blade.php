@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Documents'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-12 mt-4">
                <div class="card">
                    <div class="card-header pb-0 px-3">
                        <div class="row align-items-center mb-3">
                            <div class="col">
                                <h6 class="mb-0">{{ $document->name }}</h6>
                                {{-- <span class="text-xs">Total Page/s:
                                    <span class="text-dark ms-sm-2 font-weight-bold">{{ $document->total_pages }}</span>
                                </span> --}}
                            </div>

                            <div class="col text-end">
                                <button class="btn btn-primary btn-lg mb-0 px-0 py-1 px-2" data-bs-toggle="modal"
                                    data-bs-target="#settingsModal" role="tab" aria-selected="false">
                                    <i class="fa fa-print"></i>
                                    <span class="ms-2">Print</span>
                                </button>
                            </div>

                            <div id="alert">
                                @include('components.alert')
                            </div>
                        </div>
                        @php
                            $ext = pathinfo($document->url, PATHINFO_EXTENSION);
                            $url = storage_path('public/' . $document->url);
                            $url = env('KIOSK_BASE') . \Illuminate\Support\Facades\Storage::url($document->url);
                        @endphp
                        @if ($ext === 'pdf')
                            <iframe class="mb-5" src="{{ route('pdf.viewer', ['id' => $document->id]) }}" width="100%"
                                height="620px"></iframe>
                        @else
                            <iframe width="100%" height="620px" class="doc"
                                src="https://docs.google.com/gview?url={{ url($url) }}&embedded=true"></iframe>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settingsModalLabel">Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('transaction.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input name="document_id" type="hidden" value="{{ $document->id }}">
                        <div class="mb-2">
                            <label for="text" class="form-label">Document Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value={{ $document->name }} disabled>
                        </div>
                        <div class="mb-2">
                            <label for="color" class="form-label">Select Color</label>
                            <select class="form-select" id="color" name="color">
                                <option value="colored" selected>Colored</option>
                                <option value="black_and_white">Black and White</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="pageRange" class="form-label">Page Range</label>
                            <input type="text" class="form-control" id="pageRange" value="1 - {{$document->total_pages}}" name="page_range" readonly>
                            <input id="pageRangeSliderContainer"></input>
                        </div>
                        <div class="mb-2">
                            <label for="text" class="form-label">No of Copies</label>
                            <input type="number" class="form-control" id="no_copies" name="no_copies" min="1"
                                value=1>
                        </div>
                        <hr class=" mb-3 my-2">

                        <div class="d-flex flex-column mb-3">
                            <span class="text-xs mb-2">Prices:
                                <div>
                                    <span class="text-dark ms-sm-2 font-weight-bold">Colored: ₱
                                        {{ $price->colored_price }}</span>
                                </div>
                                <div>
                                    <span class="text-dark ms-sm-2 font-weight-bold">Black & White: ₱
                                        {{ $price->black_and_white_price }}</span>
                                </div>
                            </span>
                            <!-- Placeholder for dynamic description -->
                            <span class="text-xs">Description
                                <span class="text-dark ms-sm-2 font-weight-bold" id="colorDescription">
                                    {{ $document->total_pages }} pages (Black and White)</span>
                            </span>
                            <span class="mb-2 text-xs">TOTAL: 
                                <span id="totalAmount" class="text-dark font-weight-bold ms-sm-2"> ₱  {{$document->total_pages * $price->black_and_white_price}}</span>
                                <!-- Hidden input for request body -->
                                <input type="hidden" class="form-control" id="totalAmountInput" name="total_amount" value="">
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">Proceed to the kiosk for payment</button>
                            <button type="button" class="btn bg-gradient-dark" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- PIN modal -->
    <!-- Open PIN modal if needed -->
    @if ($message = session()->has('pinDigits'))
        <div class="modal fade" id="pinModal" aria-labelledby="pinModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pinModalLabel">PIN</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body d-flex flex-column">
                        <h5 class="text-center">Your PIN is:</h5>
                        <div class="mt-2 mb-3 d-flex flex-row justify-content-center">
                            @foreach (session()->get('pinDigits') as $digit)
                                <span class="h1 px-2 mx-1 border border-dark rounded">{{ $digit }}</span>
                            @endforeach
                        </div>
                        <p class="w-80 mx-1 align-self-center text-center">
                            This PIN will expire in 15 minutes.
                            Please proceed to the kiosk for payment.
                        </p>
                        <button type="button" class="btn bg-gradient-dark w-25 align-self-center"
                            data-bs-dismiss="modal">Close</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                (new bootstrap.Modal('#pinModal')).show();
            });
        </script>
    @endif
    <!-- jQuery -->
    <script src="{{ asset('https://code.jquery.com/jquery-3.6.0.min.js') }}"></script>

    <!-- ion-rangeslider CSS and JS -->
    <link rel="stylesheet"
        href="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/css/ion.rangeSlider.min.css') }}">
    <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/js/ion.rangeSlider.min.js') }}">
    </script>


<script>
$(document).ready(function(){
    var total_pages = {{$document->total_pages }};
    var black_and_white_price = {{ $price->black_and_white_price }};
    var colored_price = {{ $price->colored_price }};

    // Function to update description and total amount
    function updateDescription() {
        let pageRangeFrom = $("#pageRangeSliderContainer").data("from");
        let pageRangeTo = $("#pageRangeSliderContainer").data("to");
        let numCopies = parseInt($('#no_copies').val());
        let selectedColor = $('#color').val();
        let pageCount = (pageRangeTo - pageRangeFrom) + 1;

        let pricePerPage, colorTypeDescription;
        if (selectedColor === "colored") {
            pricePerPage = colored_price;
            colorTypeDescription = "Colored";
        } else if (selectedColor === "black_and_white") {
            pricePerPage = black_and_white_price;
            colorTypeDescription = "Black and White";
        } else {
            pricePerPage = 1;
            colorTypeDescription = "Unknown";
        }

        let totalPrice = pageCount * pricePerPage * numCopies
        $('#colorDescription').text(
            `${pageCount * numCopies} total pages (${colorTypeDescription})`
        );
        $('#totalAmount').text(`₱${totalPrice.toFixed(2)}`);
        $('#totalAmountInput').val(totalPrice);
    }

    $('#color').change(updateDescription);
    $('#no_copies').change(updateDescription);

    function updateSlider(data) {
        if (data.from === data.to) {
            $("#pageRange").val(data.from);
        } else {
            $("#pageRange").val(`${data.from} - ${data.to}`);
        }
        updateDescription();
    }

    $("#pageRangeSliderContainer").ionRangeSlider({
        type: "double",
        grid: true,
        min: 1,
        max: total_pages,
        from: 1,
        to: total_pages,
        onStart: updateSlider,
        onChange: updateSlider,
    });


});
</script>


@endsection
