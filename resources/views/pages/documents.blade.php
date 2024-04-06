@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Documents'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-7 mt-4">
                <div class="card">
                    <div class="card-header pb-0 px-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="mb-0">Document Information</h6>
                            </div>
                            <div class="col text-end">
                                <a class="btn bg-gradient-dark mb-0" data-bs-toggle="modal" data-bs-target="#addFileModal">
                                    <i class="fas fa-plus"></i>
                                    &nbsp;&nbsp;Add File
                                </a>
                            </div>
                        </div>
                    </div>
                    <div id="alert">
                        @include('components.alert')
                    </div>
                    <div class="card-body pt-4 p-3">
                        <ul class="list-group">
                            @foreach ($documents as $document)
                                <li class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                                    <div class="d-flex flex-column">
                                        <a class="btn btn-link p-0 m-0 text-start"
                                            href="{{ route('document.show', ['document' => $document->id]) }}">
                                            <h6 class="mb-3 text-sm "><i class="fas fa-file-csv text-lg me-1"></i>
                                                {{ $document->name }}</h6>
                                        </a>
                                        <div class="d-flex flex-row justify-content-between">
                                            <div class="d-flex flex-column">
                                                <span class="text-xs">Added: <span
                                                        class="text-dark ms-sm-2 font-weight-bold">{{ $document->created_at }}</span></span>
                                                {{-- <span class="text-xs">Total Page/s: <span
                                                        class="text-dark ms-sm-2 font-weight-bold">{{ $document->total_pages }}</span></span> --}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ms-auto text-end">
                                        @if (auth()->user()->is_admin)
                                            <a class="btn btn-link text-danger text-gradient px-3 mb-0" href="javascript:;">
                                                <i class="far fa-trash-alt me-2"></i>
                                                Delete
                                            </a>
                                        @endif

                                        @if (!auth()->user()->is_admin)
                                            <a class="btn btn-link text-dark px-3 mb-0"
                                                href="{{ route('document.show', ['document' => $document->id]) }}"><i
                                                    class="fas fa-eye text-dark me-2" aria-hidden="true"></i>View</a>
                                    </div>
                            @endif


                            </li>
                            @endforeach
                        </ul>
                        <div class="mt-1 mb-4">
                            {{ $documents->appends(['documents' => $documents->currentPage()])->links() }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5 mt-4">
                <div class="card h-100 mb-4">
                    <div class="card-header pb-0 px-3">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-0">Transaction History</h6>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end align-items-center">
                                <i class="far fa-calendar-alt me-2"></i>
                                <small>{{ now()->format('M d, Y') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-4 p-3">
                        <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Newest</h6>
                        <ul class="list-group">
                            @foreach ($transactions as $transaction)
                                <li
                                    class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                    <div class="d-flex align-items-center">
                                        <button
                                            class="btn btn-icon-only btn-rounded btn-outline-primary mb-0 me-3 btn-sm d-flex align-items-center justify-content-center"><i
                                                class="fas fa-exclamation"></i></button>
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 text-dark text-sm">{{ $transaction->document->name }} (<span
                                                    class="text-xs">{{ $transaction->created_at }}</span>)</h6>
                                            <span class="text-xs">Pages: {{ $transaction->total_pages }}</span>
                                            <span class="text-xs">Copies: {{ $transaction->no_copies }}</span>
                                            <span class="text-xs">Status: {{ $transaction->status }}</span>
                                        </div>
                                    </div>

                                    <div
                                        class="d-flex align-items-center text-danger text-gradient text-sm font-weight-bold">
                                        ₱ {{ $transaction->amount_to_be_paid }}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-1 ">
                            {{ $transactions->appends(['transactions' => $transactions->currentPage()])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
    <!-- Add File Modal -->
    <div class="modal fade" id="addFileModal" tabindex="-1" aria-labelledby="addFileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFileModalLabel">Upload File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('document.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label for="text" class="form-label">Document Name</label>
                            <input type="text" class="form-control" id="name" name="name">
                        </div>
                        <div class="mb-2">
                            <label for="file" class="form-label">Select File</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                        </div>
                        {{-- <div class="mb-2">
                            <label for="color" class="form-label">Select Color</label>
                            <select class="form-select" id="module" name="module">
                                <option value="black_and_white">Black and White</option>
                                <option value="colored">Colored</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="pageRange" class="form-label">Page Range</label>
                            <input type="text" class="form-control" id="pageRange" name="page_range" readonly>
                            <input type="hidden" id="pageRangeSlider" name="page_range_slider">
                            <div id="pageRangeSliderContainer"></div>
                        </div> --}}
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">Upload</button>
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
            $("#pageRangeSliderContainer").ionRangeSlider({
                type: "double",
                grid: true,
                min: 1,
                max: 100,
                from: 25,
                to: 75,
                onChange: function(data) {
                    $('#pageRange').val(data.from + ' - ' + data.to);
                    $('#pageRangeSlider').val(data.from + ',' + data.to);
                },
            });
        });
    </script>
@endsection
