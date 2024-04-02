<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Document;
use App\Models\Price;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class DocumentController extends Controller
{
    public function pdfViewer($id) {
        $document = Document::findOrFail($id);
        $path = Storage::path($document->url ); 
    
        if (!file_exists($path)  ) {
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
        $fileName = time() . '_' . $request->name;
        $filePath = $file->storeAs('files', $fileName);

        // Count pages in the uploaded PDF file
        $parser = new Parser();
        $pdf = $parser->parseFile(storage_path('app/' . $filePath));
        $pagesCount = count($pdf->getPages());

        // Store file upload details in the database
        Document::create([
            'user_id' => auth()->user()->id,
            'url' => $filePath,
            'name' => $request->name,
            'page_range' => "1," . $pagesCount,
            'total_pages' => $pagesCount,
        ]);

        return back()->with('succes', 'Document succesfully uploaded');
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        $price = Price::first();
        return view('pages.documents.viewPdf',compact('document', 'price'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
     

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

}
