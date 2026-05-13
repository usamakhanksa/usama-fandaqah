<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * Export data to CSV as a streamed response.
     */
    public function exportToCsv(string $filename, array $headers, $dataGenerator): StreamedResponse
    {
        return new StreamedResponse(function () use ($headers, $dataGenerator) {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($handle, $headers);
            
            // Body
            foreach ($dataGenerator() as $row) {
                fputcsv($handle, (array) $row);
            }
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }
}
