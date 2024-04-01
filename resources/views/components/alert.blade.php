<div class="px-4 pt-4">
    @if ($message = session()->has('succes'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <p class="text-white mb-0">{{ session()->get('succes') }}</p>
        </div>
    @endif
    @if ($message = session()->has('error'))
        <div class="alert alert-danger" role="alert">
            <p class="text-white mb-0">{{ session()->get('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="list-unstyled mb-0 text-white">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
</div>
