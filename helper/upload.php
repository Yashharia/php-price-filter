<?php
require '../connection.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Make sure the 'uploads' directory exists and is writable
$uploadDir = '../uploads/sheets';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}


function getName($row)
{
    $product_name = "";
    $nameKeys = ['Item Description', 'ITEM DESCRIPTION', 'DESCRIPCION', 'item description', 'DESCRIPTION']; // Add more variations as needed
    foreach ($nameKeys as $key) {
        
        if (isset($row[$key]) && $row[$key] != "") {
            $product_name = $product_name . $row[$key];
        }
    }
    $additional_details = ['UNITS X BOX', 'Item size', 'Case Pack', 'Category'];
    foreach ($additional_details as $key) {
        if (isset($row[$key]) && $row[$key] != "") {
            $product_name = $product_name . ' - '. $key . ' ' . $row[$key];
        }
    }
    return $product_name; // Default price if not found
}

function getPricing($row)
{
    $priceKeys = ['unit price', 'UNIT PRICE', 'Unit Price', 'price', 'Unit Price ']; // Add more variations as needed
    foreach ($priceKeys as $key) {
        if (isset($row[$key]) && $row[$key] != "") {
            print_r($key, $row[$key]);
            return (float) str_replace("$", "", $row[$key]);
        }
    }
    return 0.0; // Default price if not found
}

function insertProductData($data, $mysqli, $headers, $filename)
{
    // Prepare your SQL statement based on the headers, example:
    $stmt = $mysqli->prepare('INSERT INTO products (name, price, supplier_name, upc) VALUES (?,?,?,?)');

    foreach ($data as $row) {
        // Bind the data using the header keys
        $name =  getName($row);

        $pricing = getPricing($row);
        print_r($row);
        print_r($pricing);

        $supplier_name =  $filename;
        $upc =  ltrim($row['UPC'], 0);

        if ($name == "" || $pricing == "") continue;

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

$filenames = $_POST['fileName'];
$supplierNames = $_POST['supplierName'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['file']['name'] && !empty($filenames))) {

    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }

    truncate_table('products');
    truncate_table('files');

    $count = 0;

    foreach ($_FILES['file']['tmp_name'] as $index => $tmpName) {
        $originalName = $_FILES['file']['name'][$index];
        $targetFilePath = $uploadDir . '/' . basename($originalName);
        move_uploaded_file($tmpName, $targetFilePath);
    }


    foreach ($filenames as $index => $tmpName) {

        // Generate a unique file name to prevent overwriting existing files
        $targetFilePath = $uploadDir . '/' . $tmpName;

        $spreadsheet = IOFactory::load($targetFilePath);
        $worksheet = $spreadsheet->getActiveSheet();

        // Get headers from the first row
        $headerRow = $worksheet->getRowIterator(1)->current();
        $cellIterator = $headerRow->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        $headers = [];
        foreach ($cellIterator as $cell) {
            $column_heading = strtoupper($cell->getValue());
            $headers[trim($column_heading)] = $cell->getColumn();
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

        print_r($header, $supplierNames[$count]);
        insertFileData($mysqli, $supplierNames[$count], $tmpName);
        insertProductData($data, $mysqli, $headers, $supplierNames[$count]);
        $count++;
    }

    $mysqli->close();
    echo "Files have been uploaded and processed successfully.";
}
