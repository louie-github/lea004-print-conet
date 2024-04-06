<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Document;
use App\Models\Price;
use Illuminate\Http\Request;
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
        // Store publicly to easily load iframes.
        $filePath = $file->storeAs('public', $fileName);

        // Store file upload details in the database
        Document::create([
            'user_id' => auth()->user()->id,
            'url' => $filePath,
            'name' => $request->name,
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
