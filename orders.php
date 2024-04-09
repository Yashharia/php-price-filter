<?php 
require './vendor/autoload.php';

include 'header.php';
include './helper/create-order.php'; ?>

<div class="container mt-3">
    <?php if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // SQL to get all rows from the orders table
    $sql = "SELECT * FROM orders ORDER BY supplier_name";
    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        // Array to hold the orders grouped by supplier_name
        $ordersGroupedBySupplier = [];

        // Loop through all the rows
        while ($row = $result->fetch_assoc()) {
            $ordersGroupedBySupplier[$row['supplier_name']][] = $row;
        }

        // Display the orders in separate tables grouped by supplier_name
        foreach ($ordersGroupedBySupplier as $supplierName => $orders) {

            $upcs = [];
            echo "<h2>" . htmlspecialchars($supplierName) . "</h2>";
            echo "<table border='1' class='table'>";
            echo "<tr><th>Name</th><th>Price</th><th>Supplier Name</th><th>UPC</th></tr>";
            foreach ($orders as $order) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($order['name']) . "</td>";
                echo "<td>" . htmlspecialchars($order['price']) . "</td>";
                echo "<td>" . htmlspecialchars($order['supplier_name']) . "</td>";
                echo "<td>" . htmlspecialchars($order['upc']) . "</td>";
                echo "<td><button class='deleteBtn btn-danger btn' data-upc='" . htmlspecialchars($order['upc']) . "'>X</button></td>";
                echo "</tr>";
                array_push($upcs, $order['upc']);
            }
            echo "</table>";
            $sql = "SELECT filepath FROM files WHERE name = '".$supplierName."'";
            $result = $mysqli->query($sql);
            if ($result->num_rows > 0) {
                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    $downloadLink = createOrderSheet($row["filepath"], $upcs);
                    echo "<a href='" . $downloadLink . "' class='btn btn-primary'>Download file</a><br><br>";
                }
            }
        }
    } else {
        echo "No orders found.";
    }

    // Close the connection
    $mysqli->close(); ?>
</div>

<?php include 'footer.php';  ?>