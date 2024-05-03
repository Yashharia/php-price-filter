<form action="upload.php" method="POST" enctype="multipart/form-data">
    Select image to upload:
    <input type="file" name="imageUpload" id="imageUpload">
    <input type="submit" value="Upload Image" name="submit">
</form>

<?php
if (isset($_FILES['imageUpload'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["imageUpload"]["name"]);

    // Save the uploaded image in the uploads directory
    if (move_uploaded_file($_FILES["imageUpload"]["tmp_name"], $target_file)) {
        echo "The file has been uploaded.";

        // Send image to the Python service for processing
        $response = file_get_contents('http://python-service-url?image=' . urlencode($target_file));
        echo "Image recognized as: " . $response;
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>