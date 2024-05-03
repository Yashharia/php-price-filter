<?php
// Include your database connection code
require '../connection.php';

//actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'deleteOrder':
                $upc = $_POST['UPC'];
                $supplier_name = $_POST['supplier_name'];
                echo deleteOrder($upc, $supplier_name);
                break;
            case 'updateQty':
                $id = (int)$_POST['id'];
                $qty = (int)$_POST['qty'];
                echo updateOrderQuantity($id, $qty);
                break;
        }
    }
}


function deleteOrder($upc, $supplier_name)
{
    global $mysqli;
    if (!empty($upc)) {
        $stmt = $mysqli->prepare("DELETE FROM orders WHERE UPC = ? AND supplier_name = ?");
        $stmt->bind_param("ss", $upc, $supplier_name);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Order deleted successfully";
        } else {
            echo "Error deleting order";
        }

        $stmt->close();
        $mysqli->close();
    }
}

function updateOrderQuantity($id, $qty)
{
    global $mysqli;
    $stmt = $mysqli->prepare("UPDATE orders SET qty = ? WHERE id = ?");
    $stmt->bind_param("ii", $qty, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Order updated successfully";
    } else {
        echo "Error updating order";
    }

    $stmt->close();
    $mysqli->close();
}
