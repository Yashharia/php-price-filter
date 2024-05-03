<?php
require '../connection.php';

$upc = $_POST['upc'];
$supplier_name = $_POST['supplier_name'];
$qty = 1;

// Check if an entry with the same upc and supplier_name already exists
$checkSql = "SELECT COUNT(*) FROM orders WHERE upc = ? AND supplier_name = ?";
$checkStmt = $mysqli->prepare($checkSql);
$checkStmt->bind_param('ss', $upc, $supplier_name);
$checkStmt->execute();
$checkStmt->bind_result($count);
$checkStmt->fetch();
$checkStmt->close();

if ($count == 0) {
    // Entry does not exist, insert new record
    $insertSql = "INSERT INTO orders (supplier_name, upc, qty) VALUES (?, ?, ?)";
    $insertStmt = $mysqli->prepare($insertSql);
    $insertStmt->bind_param('ssi', $supplier_name, $upc, $qty);
    $insertStmt->execute();
    $insertStmt->close();
    echo "New order added successfully.";
} else {
    echo "Order with this UPC and supplier name already exists.";
}

$mysqli->close();
