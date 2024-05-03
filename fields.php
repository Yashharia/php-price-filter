<?php include 'header.php';
$sql = "SELECT field, data FROM fields";
$result = $mysqli->query($sql);

$fields = []; // Array to store the results

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        $fields[$row["field"]] = $row["data"];
    }
} 
$mysqli->close(); ?>
<div class="container main-container">
    <form id="fieldForm" enctype="multipart/form-data">

        <div class="form-group">
            <label>Names</label>
            <input type="text" class="form-control" name="names" value="<?php echo $fields['names'] ?>" placeholder="Enter name columns with comma">
        </div>
        <div class="form-group">
            <label>Additional names</label>
            <input type="text" class="form-control" name="additionalNames" value="<?php echo $fields['additionalNames'] ?>" placeholder="Enter additional columns with comma">
        </div>

        <div class="form-group">
            <label>UPC</label>
            <input type="text" class="form-control" name="upc" value="<?php echo $fields['upc'] ?>" placeholder="Enter UPC column names">
        </div>
        <div class="form-group">
            <label>Price</label>
            <input type="text" class="form-control" name="price" value="<?php echo $fields['price'] ?>" placeholder="Enter price column names">
        </div>

        <div class="form-group">
            <label>Case price</label>
            <input type="text" class="form-control" name="casePrice" value="<?php echo $fields['casePrice'] ?>" placeholder="Enter case price column names">
        </div>
        <div class="form-group">
            <label>Case pack</label>
            <input type="text" class="form-control" name="casePack" value="<?php echo $fields['casePack'] ?>" placeholder="Enter case pack column names">
        </div>

        <input type="submit" class="btn btn-primary" value="Update">

    </form>
</div>
<?php include 'footer.php'; ?>