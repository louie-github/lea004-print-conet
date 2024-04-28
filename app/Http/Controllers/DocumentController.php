<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Document;
use App\Models\Price;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;


class DocumentController extends Controller
{
    public function pdfViewer($id)
    {
        $document = Document::findOrFail($id);
        // Ignore method not found error. This exists.
        $path = Storage::disk('public')->path($document->url);

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
        $response = Http::timeout(300)->post("$backendUrl/convert", [
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
                $publicFilePath = $file->storePublicly('files', ['disk' => 'public']);
                if (!$publicFilePath) {
                    return back()->with('error', 'Failed to upload document.');
                }
                break;
            case 'doc':
            case 'docx':
            case 'xlsx':
            case 'csv':
                // TODO: Run in background
                $publicFilePath = 'files/' . $file->hashName() . '.pdf';
                $convertedFileContents = $this->convertOfficeFile($file->get(), $fileExtension);
                if (is_null($convertedFileContents)) {
                    return back()->with('error', 'Failed to convert document.');
                }
                if (!Storage::disk('public')->put($publicFilePath, $convertedFileContents)) {
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

        return back()->with('succes', 'Document successfully uploaded.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        $price = Price::first();
        return view('pages.documents.viewFile', compact('document', 'price'));
    }

    public function destroy(Document $document) {
        $numDeleted = DB::transaction(function() use ($document) {
            foreach ($document->transactions as $transaction) {
                $transaction->delete();
            }
            return $document->delete();
        });
        if ($numDeleted == 1) {
            return back()->with('succes', 'Document has been deleted.');
        } else {
            return back()->with(
                'error',
                "An unexpected error was encountered while deleting the document.
                (returned $numDeleted)"
            );
        }
    }

    private function getPageCount($fileExtension, $filePath)
    {
        $filePath = public_path('storage/' . $filePath);

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
