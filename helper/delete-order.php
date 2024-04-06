<?php
// Include your database connection code
require '../connection.php';

$upc = $_POST['UPC'];

if (!empty($upc)) {
    $stmt = $mysqli->prepare("DELETE FROM orders WHERE UPC = ?");
    $stmt->bind_param("s", $upc);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Order deleted successfully";
    } else {
        echo "Error deleting order";
    }

    $stmt->close();
    $mysqli->close();
}
?>
