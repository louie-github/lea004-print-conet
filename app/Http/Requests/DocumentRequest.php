<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:pdf,', 
            'name' => 'required|string'
        ];
    }

      /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please upload a PDF file.',
            'file.file' => 'The uploaded file must be a valid file.',
            'file.mimes' => 'The file must be in PDF format.',
            'name.required' => 'Please enter the document name.',
            'name.string' => 'The document name must be a string.'
        ];
    }
}
