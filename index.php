<?php include 'header.php'; ?>
<div class="container text-center">
    <?php $files_sql = "SELECT * from files";
    $files_result = $mysqli->query($files_sql);  ?>
    <form id="uploadForm" enctype="multipart/form-data">
        <div id="fileUploads">
            <div class="file-upload my-2">
                <input type="text" name="fileName[]" required placeholder="File Name" class="form-control mx-2">
                <input type="file" name="file[]" required accept=".xls,.xlsx" class="form-control mx-2">
            </div>
            <?php if ($files_result->num_rows > 0) {
                while ($row = $files_result->fetch_assoc()) { ?>
                    <div class="file-upload row my-2">
                        <div class="col-6">

                            <input type="text" name="fileName[]" value="<?php echo $row['name'] ?>" required placeholder="File Name" class="form-control mx-2 col-6">
                        </div>
                        <div class="col-6"><?php echo  $row['filepath'] ?></div>
                    </div>
            <?php }
            } ?>

        </div>
        <button type="button" class="btn btn-primary" id="addMore">Add More Files</button>
        <input type="submit" class="btn btn-primary" value="Upload Files">
    </form>
    <div class="row">
        <?php $sql = "
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
    price_supplier, upc
FROM 
    RankedProducts
WHERE 
    rn = 1
ORDER BY 
    name ASC;
";

        // Execute query
        $result = $mysqli->query($sql);

        if ($result->num_rows > 0) {
            echo "<table class='table'>";
            echo "<tr><th>Image</th><th>Name</th><th>UPC</th><th>Price & Supplier</th></tr>";
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td><img src='./uploads/" . $row["upc"] . ".jpg' class='product-img'/></td>";
                echo "<td>" . $row["name"] . "</td>";
                echo "<td>" . $row["upc"] . "</td>";
                // Split the price_supplier string into individual price-supplier pairs
                echo "<td>";
                $priceSuppliers = explode(',', $row["price_supplier"]);
                foreach ($priceSuppliers as $priceSupplier) {
                    // Split each pair into price and supplier
                    [$price, $supplier] = explode(' - ', $priceSupplier);
                    $formattedPrice = number_format((float)$price, 2, '.', '');
                    echo "<input type='radio' name='" . $row["upc"] . "' data-supplier='" . $supplier . "'> $" . $formattedPrice . " - " . $supplier . "</input><br>";
                }
                echo "</td>";

                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "0 results";
        } ?>
    </div>
</div>

<?php include 'footer.php'; ?>