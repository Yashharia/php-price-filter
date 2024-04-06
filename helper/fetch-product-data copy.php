<?php 
require '../connection.php';

$sql = "
WITH RankedProducts AS (
    SELECT 
        name, 
        upc,
        ROW_NUMBER() OVER(PARTITION BY upc ORDER BY LENGTH(name) DESC) as rn,
        GROUP_CONCAT(CONCAT(price, ' - ', supplier_name) ORDER BY price) as price_supplier
    FROM 
        products
    GROUP BY 
        upc
)
SELECT 
    name, 
    upc, 
    price_supplier
FROM 
    RankedProducts
WHERE 
    rn = 1
ORDER BY 
    name ASC;
";

$result = $mysqli->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'name' => $row['name'],
        'upc' => $row['upc'],
        'price_supplier' => $row['price_supplier'],
        'image' => './uploads/' . $row["upc"] . '.jpg' // Assuming images are stored in uploads directory
    ];
}

echo json_encode(['data' => $data]);
