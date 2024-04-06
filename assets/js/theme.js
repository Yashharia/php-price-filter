$(document).ready(function () {
  $("#addMore").click(function () {
    $("#fileUploads").append(
      `<div class="file-upload my-2">
            <div class="col-6">
                <input type="text" name="supplierName[]" class="form-control mx-2" placeholder="File Name">
            </div>
            <div class="col-4">
              <input type="hidden" name="fileName[]"  class='hidden-filename'> 
                <input type="file" name="file[]" class="form-control mx-2 file-input" accept=".xls,.xlsx"> 
            </div>
            <div class="remove-div col-2">
                <button class="btn btn-danger row-removal">X</button>
            </div>
      </div>`
    );
  });

  $(document).on("click", ".row-removal", function () {
    $(this).closest(".file-upload").remove();
  });

  $("#uploadForm").submit(function (e) {
    e.preventDefault();
    var formData = new FormData(this);

    $.ajax({
      url: "/product-filter/helper/upload.php",
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        alert("Files uploaded successfully");
        console.log(response);
      },
      error: function (e) {
        console.log(e);
        alert("Error uploading files");
      },
    });
  });

  // $("#product-search").select2({
  //   ajax: {
  //     url: "/product-filter/helper/fetch.php",
  //     dataType: "json",
  //     delay: 250, // wait 250 milliseconds before triggering the request
  //     data: function (params) {
  //       return {
  //         q: params.term, // search term
  //       };
  //     },
  //     processResults: function (data) {
  //       return {
  //         results: data,
  //       };
  //     },
  //     cache: true,
  //   },
  //   minimumInputLength: 3, // only start searching when at least 3 characters are entered
  //   dropdownAutoWidth: true,
  //   width: "100%",
  //   placeholder: "Search product",
  //   allowClear: true,
  // });

  $(document).on("change", ".file-input", function () {
    var fullPath = $(this).val();
    if (fullPath) {
      var startIndex =
        fullPath.indexOf("\\") >= 0
          ? fullPath.lastIndexOf("\\")
          : fullPath.lastIndexOf("/");
      var filename = fullPath.substring(startIndex);
      if (filename.indexOf("\\") === 0 || filename.indexOf("/") === 0) {
        filename = filename.substring(1);
      }
      $(this).closest(".file-upload").find(".hidden-filename").val(filename);
    }
  });

  $(document).on("click", ".radio-select", function () {
    $(".radio-select:checked").each(function () {
      var name = $(this).data("name");
      var price = $(this).data("price");
      var supplier = $(this).data("supplier");
      var upc = $(this).attr("id");
      $(this).parent().find(".clear-btn").show();

      console.log(name, price, supplier, upc);

      $.ajax({
        url: "/product-filter/helper/orders.php",
        type: "POST",
        data: { name, price, supplier, upc },
        success: function (response) {
          alert("Iten added successfully");
          console.log(response);
        },
        error: function (e) {
          console.log(e);
          alert("Error uploading files");
        },
      });
    });
  });

  $(document).on("click", ".clear-btn", function () {
    $(this)
      .parent()
      .find("input:radio:checked")
      .removeAttr("checked")
      .prop("checked", false);
    $(this).hide();
  });

  $("#productsTable").DataTable({
    "processing": true,    // Show processing indicator
    "serverSide": true,    // Enable server-side processing
    ajax: "/product-filter/helper/fetch-product-data.php",
    columns: [
      {
        data: "image",
        render: function (data, type, row) {
          return "<p></p>";
          // return '<img src="' + data + '" class="product-img"/>';
        },
      },
      { data: "name" },
      { data: "upc" },
      {
        data: "price_supplier",
        render: function (data, type, full, meta) {
          var priceSuppliers = data.split(",");
          var html = "";
          priceSuppliers.forEach(function (ps) {
            var parts = ps.split(" - ");
            var price = parseFloat(parts[0]).toFixed(2);
            var supplier = parts[1];
            html += `<input type="radio" class="radio-select" id="${full.upc}" name="${full.upc}" data-price=${price} data-supplier=${supplier} data-name="${full.name}" data-supplier="${supplier}"> 
            <label for="${full.upc}">$${price} - ${supplier} </label>
            <br>`;
          });
          html += `<button class="clear-btn btn-danger btn" style='display:none'>Clear</button>`;
          return html;
        },
      },
    ],
    paging: true,
    searching: true,
    pageLength: 250,
    "bLengthChange" : false,
    });

    $('.deleteBtn').click(function() {
      var upc = $(this).data('upc');
      if (confirm('Are you sure you want to delete this order?')) {
          $.ajax({
              url: '/product-filter/helper/delete-order.php',
              type: 'POST',
              data: {
                  'UPC': upc
              },
              success: function(response) {
                  // Refresh the page to reflect the deletion
                  location.reload();
              },
              error: function() {
                  alert('Error deleting order.');
              }
          });
      }
  });
});
