<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Document;
use App\Models\Price;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
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

    protected function convertOfficeFile(string $filePath) {
        $response = Http::post('http://172.21.80.1:48250/convert', [
            "filename" => $filePath
        ]);
        if ($response->status() === 200) {
            Storage::delete($filePath);
            return $response->json()['filename'];
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
        $publicFilePath = $file->storePublicly('files');
        switch ($fileExtension) {
            case 'pdf':
                break; // Do nothing.
            case 'doc':
            case 'docx':
            case 'xlsx':
            case 'csv':
                $publicFilePath = $this->convertOfficeFile($publicFilePath);
                if (is_null($publicFilePath)) {
                    return back()->with('error', 'Failed to convert document.');
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
