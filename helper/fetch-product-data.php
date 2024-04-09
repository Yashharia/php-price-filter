<?php
require '../connection.php';

// DataTables request parameters
$draw = $_GET['draw'];
$start = $_GET['start']; // Start record index for the current page
$length = $_GET['length']; // Number of records per page
$searchValue = $_GET['search']['value']; // Search value

// Split the search string into individual keywords
$keywords = explode(' ', $searchValue);
$searchPattern = implode('%', $keywords); // Create a pattern for SQL LIKE search

// Building the query for pagination, search, and sorting
$sql = "
SELECT 
    p.name, 
    p.upc, 
    GROUP_CONCAT(CONCAT(p.price, ' - ', p.supplier_name) ORDER BY p.price ASC) as price_supplier
FROM 
    products p
WHERE 
    p.name LIKE CONCAT('%', ?, '%') OR 
    p.upc LIKE CONCAT('%', ?, '%')
GROUP BY 
    p.upc
ORDER BY 
    p.name ASC
LIMIT ?, ?
";

// Preparing the statement
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ssii', $searchPattern, $searchPattern, $start, $length);

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'test' => 'test',
        'name' => $row['name'],
        'upc' => $row['upc'],
        'price_supplier' => $row['price_supplier'],
        'image' => './uploads/' . $row["upc"] . '.jpg'
    ];
}

// Query to get the total count of records
$totalRecordsQuery = "SELECT COUNT(DISTINCT upc) AS total FROM products";
$totalRecordsResult = $mysqli->query($totalRecordsQuery);
$totalRecordsRow = $totalRecordsResult->fetch_assoc();
$totalRecords = $totalRecordsRow['total'];

// Query to get the total count of filtered records
if($searchPattern){
    $totalFilteredRecordsQuery = "SELECT COUNT(DISTINCT upc) AS total FROM products WHERE name LIKE ? OR upc LIKE ?";
    $stmt = $mysqli->prepare($totalFilteredRecordsQuery);
    $stmt->bind_param('ss', $searchPattern, $searchPattern);
    $stmt->execute();
    $totalFilteredRecordsResult = $stmt->get_result();
    $totalFilteredRecordsRow = $totalFilteredRecordsResult->fetch_assoc();
    $totalFilteredRecords = $totalFilteredRecordsRow['total'];
}else{
    $totalFilteredRecords = $totalRecords;
}

echo json_encode([
    'test' => 'test',
    "draw" => intval($_GET['draw']),
    "recordsTotal" => intval($totalRecords) ,
    "recordsFiltered" => intval($totalFilteredRecords),
    "data" => $data
]);
