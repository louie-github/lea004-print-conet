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
                                <span class="text-xs">Total Page/s:
                                    <span class="text-dark ms-sm-2 font-weight-bold">{{ $document->total_pages }}</span>
                                </span>
                            </div>
                            <div id="alert">
                                @include('components.alert')
                            </div>

                            @if (!auth()->user()->is_admin && cache()->get('cache-current-key'))
                                <div class="col text-end">
                                    <a class="nav-link mb-0 px-0 py-1 px-2" data-bs-toggle="modal"
                                        data-bs-target="#settingsModal" role="tab" aria-selected="false">
                                        <i class="ni ni-settings-gear-65"></i>
                                        <span class="ms-2">Settings</span>
                                    </a>
                                </div>
                            @endif

                        </div>

                        <iframe class="mb-5" src="{{ route('pdf.viewer', ['id' => $document->id]) }}" width="100%"
                            height="620px"></iframe>
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
                    <form method="POST" action="{{ route('document.update', ['document' => $document->id]) }}"
                        enctype="multipart/form-data">
                        @csrf

                        @method('put')
                        <div class="mb-2">
                            <label for="text" class="form-label">Document Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value={{ $document->name }} disabled>
                        </div>
                        <div class="mb-2">
                            <label for="color" class="form-label">Select Color</label>
                            <select class="form-select" id="color" name="color">
                                <option value="black_and_white" selected>Black and White</option>
                                <option value="colored">Colored</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="pageRange" class="form-label">Page Range</label>
                            <input type="text" class="form-control" id="pageRange"
                                value="1 - {{ $document->total_pages }}" name="page_range" readonly>
                            <input type="hidden" id="pageRangeSlider" name="page_range_slider">
                            <div id="pageRangeSliderContainer"></div>
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
                                <span id="totalAmount" class="text-dark font-weight-bold ms-sm-2"> ₱
                                    {{ $document->total_pages * $price->black_and_white_price }}</span>
                                <input type="hidden" class="form-control" id="totalAmountInput" name="total_amount"
                                    value="{{ $document->total_pages * $price->black_and_white_price }}">
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
    <!-- jQuery -->
    <script src="{{ asset('https://code.jquery.com/jquery-3.6.0.min.js') }}"></script>

    <!-- ion-rangeslider CSS and JS -->
    <link rel="stylesheet"
        href="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/css/ion.rangeSlider.min.css') }}">
    <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/js/ion.rangeSlider.min.js') }}">
    </script>

    <script>
        $(document).ready(function() {
            var total_pages = {{ $document->total_pages }};
            var black_and_white_price = {{ $price->black_and_white_price }};
            var colored_price = {{ $price->colored_price }};

            $("#pageRangeSliderContainer").ionRangeSlider({
                type: "double",
                grid: true,
                min: 1,
                max: total_pages,
                from: 1,
                to: total_pages,
                onChange: function(data) {
                    var pageRangeValue = data.from === data.to ? data.from.toString() : data.from +
                        ' - ' + data.to;
                    $('#pageRange').val(pageRangeValue);
                    $('#pageRangeSlider').val(data.from + ',' + data.to);

                    // Update the details
                    updateDescription(data.from, data.to, black_and_white_price, colored_price);
                },
            });

            // Function to update description and total amount
            function updateDescription(from, to, black_and_white_price, colored_price) {
                var selectedColor = $('#color').val();
                var pageCount = (to - from) + 1; // Adjust the page count calculation

                if (selectedColor === 'colored') {
                    $('#colorDescription').text(pageCount + ' pages (Colored)');
                    var totalPrice = pageCount * colored_price;
                    $('#totalAmount').text('₱ ' + totalPrice.toFixed(2));
                } else if (selectedColor === 'black_and_white') {
                    $('#colorDescription').text(pageCount + ' pages (Black and White)');
                    var totalPrice = pageCount * black_and_white_price;
                    $('#totalAmount').text('₱ ' + totalPrice.toFixed(2));
                } else {
                    $('#colorDescription').text('Select a color to see the description');
                    $('#totalAmount').text('₱ 0.00');
                }

                $('#totalAmountInput').val(totalPrice.toFixed(2));
            }

            // Trigger updateDescription when the color selection changes
            $('#color').change(function() {
                var from = parseInt($('#pageRangeSlider').val().split(',')[0]);
                var to = parseInt($('#pageRangeSlider').val().split(',')[1]);

                if (isNaN(from) & isNaN(to)) {
                    var from = 1;
                    var to = total_pages;
                }

                // Update the details
                updateDescription(from, to, black_and_white_price, colored_price);
            });
        });
    </script>
@endsection
