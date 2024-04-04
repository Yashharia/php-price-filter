<?php
require '../connection.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Make sure the 'uploads' directory exists and is writable
$uploadDir ='../uploads/sheets';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function insertProductData($data, $mysqli, $headers, $filename)
{
    // Prepare your SQL statement based on the headers, example:
    $stmt = $mysqli->prepare('INSERT INTO products (name, price, supplier_name, upc) VALUES (?,?,?,?)');

    foreach ($data as $row) {
        // Bind the data using the header keys
        $name =  $row['ITEM DESCRIPTION'];
        $pricing =  (float) str_replace("$", "", $row['UNIT PRICE']);
        $supplier_name =  $filename;
        $upc =  ltrim($row['UPC'],0);
        if ($name == "" || $pricing == "") continue;

        print_r($filename);
        print_r($row);
        $stmt->bind_param('sdss', $name, $pricing, $supplier_name, $upc);
        $stmt->execute();
    }

    $stmt->close();
}

function insertFileData($mysqli, $name, $tmpName)
{
    $stmt = $mysqli->prepare('INSERT INTO files (name, filepath) VALUES (?,?)');
    $stmt->bind_param('ss', $name, $tmpName);
    $stmt->execute();
    $stmt->close();
}

function truncate_table($tableName)
{
    global $mysqli;
    $query = "TRUNCATE TABLE `$tableName`";
    if ($mysqli->query($query) === TRUE) {
        echo "Table $tableName has been emptied successfully.";
    } else {
        echo "Error emptying table: " . $mysqli->error;
    }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['file']['name'] && !empty($_POST['fileName']))) {

    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }

    truncate_table('files');
    truncate_table('products');

    $filename = $_POST['fileName'];
    $count = 0;

    foreach ($_FILES['file']['tmp_name'] as $index => $tmpName) {

        // Generate a unique file name to prevent overwriting existing files
        $originalName = $_FILES['file']['name'][$index];
        $targetFilePath = $uploadDir . '/' . basename($originalName);

        print_r($targetFilePath);
        if (move_uploaded_file($tmpName, $targetFilePath)) {

            $spreadsheet = IOFactory::load($targetFilePath);
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

            insertFileData($mysqli, $filename[$count], $originalName);
            insertProductData($data, $mysqli, $headers, $filename[$count]);
            $count++;
        }
    }

    $mysqli->close();
    echo "Files have been uploaded and processed successfully.";
}
