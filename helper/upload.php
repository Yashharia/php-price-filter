<?php
require '../connection.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Make sure the 'uploads' directory exists and is writable
$uploadDir = '../uploads/sheets';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$sql = "SELECT field, data FROM fields";
$result = $mysqli->query($sql);

$fields = []; // Array to store the results

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $fields[$row["field"]] = explode(", ", $row["data"]);
    }
}

function getName($row)
{
    global $fields;
    $product_name = "";
    $nameKeys = $fields['names']; // Add more variations as needed
    foreach ($nameKeys as $key) {

        if (isset($row[$key]) && $row[$key] != "") {
            $product_name = $product_name . $row[$key];
        }
    }
    $additional_details = $fields['additionalNames'];
    foreach ($additional_details as $key) {
        if (isset($row[$key]) && $row[$key] != "") {
            $product_name = $product_name . ' - ' . strtoupper($key) . ' ' . $row[$key];
        }
    }
    return $product_name; // Default price if not found
}

function getUPC($row)
{
    global $fields;
    $product_upc = "";
    $nameKeys = $fields['upc']; // Add more variations as needed
    foreach ($nameKeys as $key) {
        if (isset($row[$key]) && $row[$key] != "") {
            $product_upc = str_replace("-", "", $row[$key]);
        }
    }
    return $product_upc; // Default price if not found
}
function getPricing($row)
{
    global $fields;
    $priceKeys = $fields['price']; // Add more variations as needed
    foreach ($priceKeys as $key) {
        if (isset($row[$key]) && $row[$key] != "") {
            // print_r($key, $row[$key]);
            $value = str_replace("$", "", $row[$key]);  // Remove the dollar sign
            $formattedValue = number_format((float)$value, 2, '.', '');  // Convert to float and format to 2 decimal places

            return $formattedValue;
        }
    }
    return 0.0; // Default price if not found
}
function getCasePricing($row)
{
    global $fields;
    $priceKeys = $fields['casePrice']; // Add more variations as needed
    foreach ($priceKeys as $key) {
        if (isset($row[$key]) && $row[$key] != "") {
            print_r($key, $row[$key]);
            $case_price = (float) str_replace("$", "", $row[$key]);
            return number_format($case_price, 2);
        }
    }

    $casePack = $fields['casePack'];
    foreach ($casePack as $key) {
        if (isset($row[$key]) && $row[$key] != "") {
            $unitPrice = getPricing($row);
            $case_price =  (float) str_replace("$", "", $row[$key]) * $unitPrice;
            return number_format($case_price, 2);
        }
    }
    return 0.0; // Default price if not found
}


function insertProductData($data, $mysqli, $headers, $filename, $currency)
{
    // Prepare your SQL statement based on the headers, example:
    $stmt = $mysqli->prepare('INSERT INTO products (name, price, case_price, supplier_name, upc, currency) VALUES (?,?,?,?,?,?)');

    foreach ($data as $row) {
        // Bind the data using the header keys
        $name =  getName($row);

        $pricing = getPricing($row);
        $casePrice = getCasePricing($row);
        print_r($row);

        $supplier_name =  $filename;
        $upc =  ltrim(getUPC($row), 0);


        // print_r($name. " - ". $pricing." - ". $upc. "     ----     ");
        if ($name == "" || $upc == "") continue;

        $stmt->bind_param('sddsss', $name, $pricing, $casePrice, $supplier_name, $upc, $currency);
        $stmt->execute();
    }

    $stmt->close();
}

function insertFileData($mysqli, $name, $tmpName, $currency)
{
    $stmt = $mysqli->prepare('INSERT INTO files (name, filepath, currency) VALUES (?,?,?)');
    $stmt->bind_param('sss', $name, $tmpName, $currency);
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
$currencys = $_POST['currency'];


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
            $column_heading = strtolower($cell->getValue());
            echo ltrim($column_heading);
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

        // print_r($header, $supplierNames[$count], $currencys[$count]);
        insertFileData($mysqli, $supplierNames[$count], $tmpName, $currencys[$count]);
        insertProductData($data, $mysqli, $headers, $supplierNames[$count], $currencys[$count]);
        $count++;
    }

    $mysqli->close();
    echo "Files have been uploaded and processed successfully.";
}
