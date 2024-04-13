@php
    $ext = pathinfo($document->url, PATHINFO_EXTENSION);
    $url = config('app.url') . \Illuminate\Support\Facades\Storage::url($document->url);
@endphp
@if ($ext === 'pdf')
    <iframe class="mb-5" src="{{ route('pdf.viewer', ['id' => $document->id]) }}" width="100%" height="620px"></iframe>
@else
    <iframe width="100%" height="620px" class="doc"
        src="https://view.officeapps.live.com/op/embed.aspx?src={{ url($url) }}"></iframe>
@endif
