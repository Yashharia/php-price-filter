<?php include 'header.php'; ?>
<div class="container main-container">
    <?php $files_sql = "SELECT * from files";
    $files_result = $mysqli->query($files_sql);  ?>
    <form id="uploadForm" enctype="multipart/form-data">
        <div id="fileUploads">
            <div class="file-upload row my-2">
                <div class="col-4"><input type="text" name="supplierName[]" required placeholder="File Name" class="form-control mx-2"></div>
                <div class="col-3"><input type="hidden" class='hidden-filename' name="fileName[]"> <input type="file" name="file[]" required accept=".xls,.xlsx" class="form-control mx-2 file-input"></div>
                <div class="col-3">
                    <select class="form-control" name="currency[]">
                        <option value="$">USD - $</option>
                        <option value="€">EUR - €</option>
                    </select>
                </div>
            </div>
            <?php if ($files_result->num_rows > 0) {
                while ($row = $files_result->fetch_assoc()) { ?>
                    <div class="file-upload row my-2">
                        <div class="col-4"><input type="text" name="supplierName[]" value="<?php echo $row['name'] ?>" required placeholder="File Name" class="form-control mx-2 col-6"></div>
                        <div class="col-3"><input type="hidden" class='hidden-filename' name="fileName[]" value="<?php echo  $row['filepath'] ?>"><?php echo  $row['filepath'] ?></div>
                        <div class="col-3">
                            <select class="form-control" name="currency[]">
                                <option value="$" <?php echo ($row['currency'] == "$")? "selected" : "" ?>>USD - $</option>
                                <option value="€" <?php echo ($row['currency'] == "€")? "selected" : "" ?>>EUR - €</option>
                            </select>
                        </div>
                        <div class="remove-div col-2"><button class="btn btn-danger row-removal">X</button></div>
                    </div>
            <?php }
            } ?>

        </div>
        <button type="button" class="btn btn-primary" id="addMore">Add More Files</button>
        <input type="submit" class="btn btn-primary" value="Upload Files">
    </form>
    <!-- <div class="row my-4">
        <select id="product-search" class="form-control w-100" style="width: 300px;"></select>
    </div> -->

    <div class="row my-3">
        <table id="productsTable" class="display hover stripe row-border">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>UPC</th>
                    <th>Price & Supplier</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>