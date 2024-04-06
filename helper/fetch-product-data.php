<?php 
require '../connection.php';

// DataTables request parameters
$start = $_GET['start']; // Start record index for the current page
$length = $_GET['length']; // Number of records per page
$searchValue = $_GET['search']['value']; // Search value

// Building the query for pagination, search, and sorting
$sql = "
WITH FilteredProducts AS (
    SELECT 
        p.name, 
        p.upc,
        CONCAT(p.price, ' - ', p.supplier_name) AS price_supplier,
        p.price,
        p.supplier_name,
        ROW_NUMBER() OVER(PARTITION BY p.upc ORDER BY LENGTH(p.name) DESC, p.price) as rn
    FROM 
        products p
    WHERE 
        p.name LIKE ?
    OR 
        p.upc LIKE ?
)
SELECT 
    name, 
    upc, 
    GROUP_CONCAT(price_supplier ORDER BY price) as price_supplier
FROM 
    FilteredProducts
WHERE 
    rn = 1
GROUP BY 
    upc
LIMIT ?, ?
";

// Preparing the statement
$stmt = $mysqli->prepare($sql);
$searchPattern = '%' . $searchValue . '%';
$stmt->bind_param('ssii', $searchPattern, $searchPattern, $start, $length);

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
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
$totalFilteredRecordsQuery = "SELECT COUNT(DISTINCT upc) AS total FROM products WHERE name LIKE ? OR upc LIKE ?";
$stmt = $mysqli->prepare($totalFilteredRecordsQuery);
$stmt->bind_param('ss', $searchPattern, $searchPattern);
$stmt->execute();
$totalFilteredRecordsResult = $stmt->get_result();
$totalFilteredRecordsRow = $totalFilteredRecordsResult->fetch_assoc();
$totalFilteredRecords = $totalFilteredRecordsRow['total'];

echo json_encode([
    "draw" => intval($_GET['draw']),
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($totalFilteredRecords),
    "data" => $data
]);