$(document).ready(function () {
    $('#addMore').click(function () {
        $('#fileUploads').append('<div class="file-upload my-2"><input type="text" name="fileName[]" class="form-control mx-2" placeholder="File Name"><input type="file" name="file[]" class="form-control mx-2" accept=".xls,.xlsx"></div>');
    });

    $('#uploadForm').submit(function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        
        $.ajax({
            url: '/product-filter/helper/upload.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                alert('Files uploaded successfully');
                console.log(response);
            },
            error: function (e) {
                console.log(e);
                alert('Error uploading files');
            }
        });
    });
});