<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Document;
use App\Models\Price;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;


class DocumentController extends Controller
{
    public function pdfViewer($id)
    {
        $document = Document::findOrFail($id);
        $path = Storage::path($document->url);

        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        $file = file_get_contents($path);
        $response = response()->make($file, 200);
        $response->header('Content-Type', 'application/pdf');
        return $response;
    }

    protected function convertOfficeFile(string $file, string $fileExtension) {
        $backendUrl = config('app.backend_url');
        $response = Http::timeout(30)->post("$backendUrl/convert", [
            "data" => base64_encode($file),
            "extension" => $fileExtension,
        ]);
        if ($response->status() === 200) {
            return base64_decode($response->json()['data']);
        } else {
            return null;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentRequest $request)
    {
        $file = $request->file('file');
        // Detect via MIME type instead of using client extension
        $fileExtension = $file->extension();
        $publicFilePath = null;
        switch ($fileExtension) {
            case 'pdf':
                $publicFilePath = $file->storePublicly(public_path('files'));
                if (!$publicFilePath) {
                    return back()->with('error', 'Failed to upload document.');
                }
                break;
            case 'doc':
            case 'docx':
            case 'xlsx':
            case 'csv':
                // TODO: Run in background
                $publicFilePath = public_path('files') . '/' . $file->hashName() . '.pdf';
                $convertedFileContents = $this->convertOfficeFile($file->get(), $fileExtension);
                if (is_null($convertedFileContents)) {
                    return back()->with('error', 'Failed to convert document.');
                }
                if (!Storage::put($publicFilePath, $convertedFileContents)) {
                    return back()->with('error', 'Failed to upload document.');
                }
                break;
            default:
                // TODO: Handle unsupported file extensions or other cases
                return back()->with('error', 'Unsupported file type.');
        }

        $pageCount = $this->getPageCount('pdf', $publicFilePath);
        Document::create([
            'user_id' => auth()->user()->id,
            'url' => $publicFilePath,
            'name' => $request->name,
            'page_range' => "1," . $pageCount,
            'total_pages' => $pageCount,
        ]);

        return back()->with('succes', 'Document succesfully uploaded');
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        $price = Price::first();
        return view('pages.documents.viewFile', compact('document', 'price'));
    }

    private function getPageCount($fileExtension, $filePath)
    {
        $filePath = storage_path('app/' . $filePath);

        switch ($fileExtension) {
            case 'pdf':
                return $this->countPdfPages($filePath);
            case 'docx':
                return $this->countWordPages($filePath);
            case 'xlsx':
                return $this->countExcelPages(request());
            case 'csv':
                return $this->countExcelPages(request());
            default:
                return 0; // Handle unsupported file extensions or other cases
        }
    }
}
