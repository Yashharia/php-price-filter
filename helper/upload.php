<?php
require '../connection.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function insertProductData($data, $mysqli, $headers, $filename)
{
    // Prepare your SQL statement based on the headers, example:
    $stmt = $mysqli->prepare('INSERT INTO products (name, price, supplier_name, upc) VALUES (?,?,?,?)');

    foreach ($data as $row) {
        // Bind the data using the header keys
        $name =  $row['ITEM DESCRIPTION'];
        $pricing =  (float) str_replace("$", "", $row['UNIT PRICE']);
        $supplier_name =  $filename;
        $upc =  $row['UPC'];
        if($name == "" || $pricing == "") continue;

        print_r($filename);
        print_r($row);
        $stmt->bind_param('sdss', $name, $pricing, $supplier_name, $upc);
        $stmt->execute();
    }

    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['file']['name'] && !empty($_POST['fileName']))) {
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }

    $filename = $_POST['fileName'];
    $count = 0;
    foreach ($_FILES['file']['tmp_name'] as $index => $tmpName) {
        $spreadsheet = IOFactory::load($tmpName);
        $worksheet = $spreadsheet->getActiveSheet();

        // Get headers from the first row
        $headerRow = $worksheet->getRowIterator(1)->current();
        $cellIterator = $headerRow->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        $headers = [];
        foreach ($cellIterator as $cell) {
            $column_heading = strtoupper($cell->getValue());
            $headers[$column_heading] = $cell->getColumn();
        }

        // Read data rows
        $data = [];
        foreach ($worksheet->getRowIterator(2) as $row) { // Start from the second row
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $header = array_search($cell->getColumn(), $headers);
                $rowData[$header] = $cell->getValue();
            }
            $data[] = $rowData;
        }

        insertProductData($data, $mysqli, $headers, $filename[$count]);
        $count++;
    }

    $mysqli->close();
    echo "Files have been uploaded and processed successfully.";
}
