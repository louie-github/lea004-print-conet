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