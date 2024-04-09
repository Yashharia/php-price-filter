<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

function createOrderSheet($fileName, $upcToKeep)
{

    $inputFileName = './uploads/sheets/'.$fileName;
    $outputFileName = './uploads/sheets/OrderSheet'.$fileName;

    $spreadsheet = IOFactory::load($inputFileName);
    $sheet = $spreadsheet->getActiveSheet();

    $filteredRows = [];
    $upcColumn = null;

    // Find the column with the header 'UPC' and copy headers
    $headerRow = [];
    foreach ($sheet->getRowIterator() as $row) {
        if ($row->getRowIndex() == 1) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                $headerRow[$cell->getColumn()] = $cell->getValue();
                if (trim($cell->getValue()) == 'UPC') {
                    $upcColumn = $cell->getColumn();
                }
            }
            $filteredRows[] = $headerRow; // Add header row to the filtered data
            break; // Exit loop after processing header row
        }
    }

    if ($upcColumn === null) {
        echo "UPC column not found.";
        exit;
    }

    // Filter rows based on UPC values
    foreach ($sheet->getRowIterator() as $row) {
        if ($row->getRowIndex() == 1) {
            continue; // Skip header row as it's already added
        }

        $upcCell = $sheet->getCell($upcColumn . $row->getRowIndex());
        if (in_array($upcCell->getValue(), $upcToKeep)) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[$cell->getColumn()] = $cell->getValue();
            }

            $filteredRows[] = $rowData;
        }
    }

    if (count($filteredRows) <= 1) { // Only header row is present
        echo "No rows match the specified UPC values.";
    } else {
        $newExcel = new Spreadsheet();
        $newSheet = $newExcel->getActiveSheet();

        $rowNumber = 1;
        foreach ($filteredRows as $rowData) {
            foreach ($rowData as $column => $value) {
                $newSheet->setCellValue($column . $rowNumber, $value);
            }
            $rowNumber++;
        }

        $writer = IOFactory::createWriter($newExcel, 'Xlsx');
        $writer->save($outputFileName);

        return $outputFileName;
    }
}
