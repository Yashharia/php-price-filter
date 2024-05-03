<?php 
require '../connection.php';

$stmt = $mysqli->prepare("UPDATE fields SET data = ? WHERE field = ?");
$stmt->bind_param("ss", $value, $field);

// Array of fields and values from POST data
$fields = [
    'names' => $_POST['names'],
    'additionalNames' => $_POST['additionalNames'],
    'upc' => $_POST['upc'],
    'price' => $_POST['price'],
    'casePrice' => $_POST['casePrice'],
    'casePack' => $_POST['casePack']
];

// Execute update for each field
foreach ($fields as $field => $value) {
    $stmt->execute();
}

echo "Records updated successfully";

$stmt->close();
$mysqli->close();

