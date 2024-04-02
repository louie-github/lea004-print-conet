@extends('layouts.app-kiosk')
@section('content')
    <main class="main-content mt-0 vh-100 d-flex justify-content-around align-items-center">
        <section id="leftSide" class="w-75 d-flex flex-column align-items-center">
            <h5 class="w-50 text-center">Visit our app by scanning the QR code below!</h5>
            <div id="kioskQr" class="my-3">
                @php
                    $url = env('KIOSK_URL');
                @endphp
                {!!
                    QrCode::size(300)
                        ->backgroundColor(255, 255, 255)
                        ->color(153, 33, 3)
                        ->margin(1)
                        ->generate($url)
                !!}
            </div>
            <a class="mt-6 btn btn-primary btn-lg h4" href="/kiosk/pin">Enter PIN</a>
        </section>
    </main>
@endsection
