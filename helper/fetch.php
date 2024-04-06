<?php 
require '../connection.php';

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$searchTerm = $_GET['q']; // Assuming you pass the search term via query string

if (strlen($searchTerm) >= 3) {
    $sql = "SELECT name FROM products WHERE name LIKE ?";
    $stmt = $mysqli->prepare($sql);
    $likeTerm = '%' . $searchTerm . '%';
    $stmt->bind_param("s", $likeTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = ['id' => $row['name'], 'text' => $row['name']];
    }

    echo json_encode($data);
}

$mysqli->close();
