<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Document;
use App\Models\Price;
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentRequest $request)
    {
        $file = $request->file('file');
        $fileExtension = $file->getClientOriginalExtension();
        $fileName = time() . '_' . $request->name . '.' . $fileExtension;
        $filePath = $file->storeAs('public', $fileName);

        $pageCount = $this->getPageCount($fileExtension, $filePath);
        Document::create([
            'user_id' => auth()->user()->id,
            'url' => $filePath,
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
