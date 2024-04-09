<?php
$imgPath = realpath(dirname(__FILE__) . '/../uploads'); // Adjust the path to your images folder
$folderPath = str_replace($imgPath, "\\", "/");
$images = array_slice(array_diff(scandir($folderPath), array('.', '..')), $_POST['offset'], $_POST['limit']);

foreach ($images as $image) {
    if (is_file($folderPath . '/' . $image)) {
        echo '<div class="col-lg-3 col-md-4 col-sm-6 mb-4">';
        echo '<img src="' . $folderPath . '/' . $image . '" class="img-fluid" alt="Image">';
        echo '</div>';
    }
}
