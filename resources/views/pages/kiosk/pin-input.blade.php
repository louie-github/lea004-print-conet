@extends('layouts.app-kiosk')

<style>
    .digitInput {
        width: 1.5em;
    }

    .pinPad button {
        width: 2em;
    }

</style>

@section('content')
    <main class="main-content mt-0 vh-100 d-flex justify-content-around align-items-center">
        <div class="w-75 d-flex flex-column align-items-center">

            <div id="alert">
                @include('components.alert')
            </div>

            <h4 class="w-50 text-center">Enter your transaction PIN:</h4>

            <form id="pinForm" action="{{ route('kiosk.pinTransaction') }}" method="post" class="my-3">
                @csrf
                <input type="text" name="pin" id="pin" value="" hidden>
                @for ($i = 0; $i < 6; $i++)
                    <input type="text" name="digit{{ $i }}" id="digit{{ $i }}"
                        class="digitInput h1 text-center border border-dark rounded mx-1" disabled>
                @endfor
            </form>

            <div id="pinPad" class="pinPad btn-group-vertical" role="group">
                <?php
                    $digits = array(1,2,3,4,5,6,7,8,9,
                                '<span style="font-size: 0.5em;">CLEAR</span>',0,
                                '<i class="fas fa-backspace"></i>');
                    for ($group_i = 0; $group_i < 4; $group_i++) {
                        echo "<div class='btn-group'>\n";
                        for ($i = 0; $i < 3; $i++){
                            $new_i = 3*$group_i + $i;
                            $value = $digits[$new_i];
                            echo "<button type='button'
                                   class='btn btn-outline-primary my-0 h1 font-monospace'
                                   onclick='pinInput($new_i)'>$value</button>\n";
                        }
                        echo "</div>\n";
                    }
                ?>
            </div>

            <div class="mt-6 d-flex align-items-center">
                <button class="mx-4 btn btn-primary btn-lg h5" onclick="submitPin()">Submit</button>
                <a class="mx-4 btn btn-secondary btn-lg h5" href="/kiosk/qr">Cancel</a>
            </div>

        </div>

        <script>
            let pinPad = document.getElementById("pinPad");
            let pinForm = document.getElementById("pinForm");
            let pinFormInput = document.getElementById("pin");
            let inputs = Array.from(document.querySelectorAll("#pinForm input.digitInput")).filter(e => e.id != "pin");
            function findNextEmptyInput() {
                for (let i = 0; i < inputs.length; i++) {
                    if (!inputs[i].value) return inputs[i];
                }
            }
            function findNextFilledInput() {
                for (let i = inputs.length - 1; i >= 0; i--) {
                    if (inputs[i].value) return inputs[i];
                }
            }
            function pinInput(value) {
                // value 0 - 8 correspondes to 1 - 9,
                // value 9     corresponds to CLEAR,
                // value 10    corresponds to 0,
                // value 11    corresponds to BACKSPACE,
                if (value < 9) {
                    findNextEmptyInput().value = value + 1;
                }
                else if (value == 9) {
                    // Clear all inputs
                    inputs.forEach((inp) => inp.value = "");
                }
                else if (value == 10) {
                    findNextEmptyInput().value = 0;
                }
                else if (value == 11) {
                    findNextFilledInput().value = '';
                }
            }
            function submitPin() {
                let pin = inputs.map(e => e.value).join("");
                pinFormInput.value = pin;
                pinForm.submit();
            }
        </script>
    </main>
@endsection
