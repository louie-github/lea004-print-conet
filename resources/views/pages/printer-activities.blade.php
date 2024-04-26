@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Tables'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h5>Printer Status</h5>
                    </div>
                    <div class="card-body pt-3">
                        <form action="{{ route('configurePrinting') }}" method="POST">
                            @csrf
                            <div class="row">
                                <label for="printerSelect" class="col-auto col-form-label pe-0">
                                    <h6>Select printer:</h6>
                                </label>
                                <div class="col-6">
                                    <select class="form-select" name="printerSelect" id="printerSelect"
                                            onchange="setDetails();">
                                        @foreach ($printers as $printer)
                                            @if ($printer === $selectedPrinter)
                                                <option selected>{{ $printer }}</option>
                                            @else
                                                <option>{{ $printer }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-3">
                                    <button type="submit" class="btn btn-primary">
                                        Set as active
                                    </button>
                                </div>
                            </div>
                        </form>
                        {{-- This feels really hacky. There must be a better way. --}}
                        <ul class="list-group list-group-flush ms-3 w-90">
                            <li class="list-group-item d-flex">
                                <h6 class="my-0">Name: </h6>
                                <h6 class="my-0 ms-1 font-weight-normal" id="printerName"></h6>
                            </li>
                            <li class="list-group-item d-flex">
                                <h6 class="my-0">Port: </h6>
                                <h6 class="my-0 ms-1 font-weight-normal" id="printerPort"></h6>
                            </li>
                            <li class="list-group-item d-flex">
                                <h6 class="my-0">Driver: </h6>
                                <h6 class="my-0 ms-1 font-weight-normal" id="printerDriver"></h6>
                            </li>
                            <li class="list-group-item d-flex">
                                <h6 class="my-0">Status: </h6>
                                <h6 class="my-0 ms-1 font-weight-normal" id="printerStatus"></h6>
                            </li>
                            <li class="list-group-item d-flex">
                                <h6 class="my-0">Raw status code: </h6>
                                <h6 class="my-0 ms-1 font-weight-normal" id="printerRawStatus"></h6>
                            </li>
                            <li class="list-group-item d-flex">
                                <h6 class="my-0">Jobs: </h6>
                                <h6 class="my-0 ms-1 font-weight-normal" id="printerJobs"></h6>
                            </li>
                        </ul>
                    </div>
                    <div id="alert">
                        @include('components.alert')
                    </div>
                </div>
            </div>
        </div>

        <script>
            const STATUS_ROUTE = "{{ route('printerStatus') }}/"
            async function setDetails() {
                const selectElement = document.getElementById('printerSelect');
                const selectedValue = selectElement.options[selectElement.selectedIndex].value;
                const response = await fetch(STATUS_ROUTE + encodeURI(selectedValue));
                const data = await response.json();
                document.getElementById('printerName').textContent = data.status.name;
                document.getElementById('printerPort').textContent = data.status.port;
                document.getElementById('printerDriver').textContent = data.status.driver;
                document.getElementById('printerJobs').textContent = data.status.jobs;
                // Special case: data.status.status is a list
                if (data.status.status.length < 1) {
                    document.getElementById('printerStatus').textContent = '✅ OK';
                } else {
                    document.getElementById('printerStatus').textContent = data.status.status.join(', ');
                }
                // Special case: data.status.raw_status should be shown
                // as a hex code
                document.getElementById('printerRawStatus').textContent = `
                0x${data.status.raw_status.toString(16).toUpperCase().padStart(8, '0')}
                (${data.status.raw_status})
                `
            }
            document.addEventListener("DOMContentLoaded", (event) => {
                setDetails();
            });
        </script>

        @include('layouts.footers.auth.footer')
    </div>
@endsection
