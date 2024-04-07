<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Smalot\PdfParser\Parser;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function countPdfPages($filePath)
    {
        $parser = new Parser();
        $pdf = $parser->parseFile(storage_path('app/' . $filePath));
        return count($pdf->getPages());
    }

    public function countWordPages($filePath)
    {
        // This approach only works with DOCX files; it cannot read/count pages in DOC file extensions.
        $zip = new \PhpOffice\PhpWord\Shared\ZipArchive();
        if (!$zip) {
            return 0;
        }
        $zip->open($filePath);

        $xml = new \DOMDocument();
        $xml->loadXML($zip->getFromName("docProps/app.xml"));
        return $xml->getElementsByTagName('Pages')->item(0)->nodeValue;
    }

    public function countExcelPages($request)
    {
        $excelFilePath = $request->file('file')->getRealPath();

        // Load Excel file
        $spreadsheet = IOFactory::load($excelFilePath);

        // Create Dompdf instance
        $dompdf = new Dompdf();
        $dompdf->setPaper('A4', 'portrait');

        // Start with an empty HTML content
        $html = '';

        // Iterate through each sheet in the Excel file
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $spreadsheet->setActiveSheetIndexByName($sheetName);
            $sheet = $spreadsheet->getActiveSheet();

            // Convert sheet data to HTML
            $sheetHtml = '<table>';
            foreach ($sheet->getRowIterator() as $row) {
                $sheetHtml .= '<tr>';
                foreach ($row->getCellIterator() as $cell) {
                    $sheetHtml .= '<td>' . $cell->getValue() . '</td>';
                }
                $sheetHtml .= '</tr>';
            }
            $sheetHtml .= '</table>';

            $html .= $sheetHtml;

            // Add a page break after each sheet (optional)
            $html .= '<div style="page-break-after: always;"></div>';
        }

        // Load HTML content into Dompdf and render PDF
        $dompdf->loadHtml($html);
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        return  $canvas->get_page_count();
    }

    public function countSheets($filePath)
    {
        //count excel sheet
        $sheetNames = Excel::toArray([], $filePath);
        return count($sheetNames);
    }
}
