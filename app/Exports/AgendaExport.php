<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class AgendaExport
{
    protected $users;
    protected $activityName;

    public function __construct($users, $activityName)
    {
        $this->users = $users;
        $this->activityName = $activityName;
    }

    public function export()
    {
        // Clear output buffer to prevent corruption
        if (ob_get_length()) {
            ob_end_clean();
        }

        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Headers
            $headers = ['Naam', 'Tussenvoegsel', 'Achternaam', 'Email', 'Aanwezig', 'Datum'];
            $sheet->fromArray([$headers], NULL, 'A1');

            // Add data
            $row = 2;
            foreach ($this->users as $user) {
                $presenceStatus = match ($user["presence"] ?? 'null') {
                    'present' => 'Aangemeld',
                    'absent'  => 'Afgemeld',
                    default   => 'Niet gemeld',
                };

                // Determine presence date or use '-'
                $presenceDate = $user["date"] !== '-' && !empty($user["date"])
                    ? Carbon::parse($user["date"])->format('d-m-Y H:i')
                    : '-';

                $rowData = [
                    $user["name"],
                    $user["infix"],
                    $user["last_name"],
                    $user["email"],
                    $presenceStatus,
                    $presenceDate,
                ];

                // Set the row data
                $sheet->fromArray([$rowData], NULL, 'A' . $row);

                // Apply background color to the "Aanwezig" (presence) column
                $presenceColumn = 'E' . $row; // "E" column corresponds to 'Aanwezig'

                // Apply colors based on the presence status
                if ($presenceStatus == 'Aangemeld') {
                    $sheet->getStyle($presenceColumn)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                    $sheet->getStyle($presenceColumn)->getFill()->getStartColor()->setARGB('FF00FF00'); // Green
                } elseif ($presenceStatus == 'Afgemeld') {
                    $sheet->getStyle($presenceColumn)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                    $sheet->getStyle($presenceColumn)->getFill()->getStartColor()->setARGB('FFFF0000'); // Red
                }

                $row++;
            }

            // Auto size columns
            foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Set auto filter
            $lastColumn = $sheet->getHighestColumn();
            $sheet->setAutoFilter("A1:{$lastColumn}1");

            // Return streamed response instead of saving
            $filename = 'presence_export_' . date('d-m-Y_H-i-s') . '.xlsx';

            return new StreamedResponse(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output'); // Direct output to browser
            }, 200, [
                "Content-Type" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "Content-Disposition" => "attachment; filename=\"$filename\"",
                "Cache-Control" => "max-age=0",
            ]);

        } catch (\Exception $e) {
            \Log::error('Excel export failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to export Excel file.'], 500);
        }
    }

}
