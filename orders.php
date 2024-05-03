<?php
require './vendor/autoload.php';

include 'header.php';
include './helper/create-order.php'; ?>

<div class="container mt-3 main-container">
    <?php
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
            echo "<tr><th>Name</th><th>Price</th><th>Supplier Name</th><th>UPC</th><th>qty</th></tr>";

            $stmt = $mysqli->prepare("SELECT name, price FROM products WHERE supplier_name = ? AND upc = ?");

            foreach ($orders as $order) {

                $stmt->bind_param("ss", $order['supplier_name'], $order['upc']);
                $stmt->execute();
                $result = $stmt->get_result();

                $name = '';
                $price = '';

                while ($row = $result->fetch_assoc()) {
                    $name = $row['name'];
                    $price = $row['price'];
                }

                echo "<tr>";
                echo "<td>" . htmlspecialchars($name) . "</td>";
                echo "<td>" . htmlspecialchars($price) . "</td>";
                echo "<td>" . htmlspecialchars($order['supplier_name']) . "</td>";
                echo "<td>" . htmlspecialchars($order['upc']) . "</td>";
                echo "<td><input name='qty' class='order-qty-update' type='number' value='" . $order['qty'] . "' data-id='" . $order['id'] . "'/></td>";
                echo "<td><button class='deleteBtn btn-danger btn' data-upc='" . htmlspecialchars($order['upc']) . "'>X</button></td>";
                echo "</tr>";
                $upcs[$order['upc']] = $order['qty'];
            }
            echo "</table>";
            $sql = "SELECT filepath FROM files WHERE name = '" . $supplierName . "'";
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