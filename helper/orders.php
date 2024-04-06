<?php
require '../connection.php';

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$name = $_POST['name'];
$price = $_POST['price'];
$supplier = $_POST['supplier'];
$upc = $_POST['upc'];

print_r($name);

$sql = "INSERT into orders (name, price, supplier_name, upc) VALUES (?,?,?,?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('sdss', $name, $price, $supplier, $upc);
$stmt->execute();
$stmt->close();

$mysqli->close();
