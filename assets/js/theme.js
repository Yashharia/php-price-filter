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
      url: current_website + "helper/upload.php",
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      beforeSend: function () {
        $(".main-container").addClass("loading");
      },
      success: function (response) {
        alert("Files uploaded successfully");
        console.log(response);
        $(".main-container").removeClass("loading");

        // location.reload();
      },
      error: function (e) {
        console.log(e);
        alert("Error uploading files");
      },
    });
  });

  $("#fieldForm").submit(function (e) {
    e.preventDefault();
    var formData = new FormData(this);

    $.ajax({
      url: current_website + "helper/uploadFields.php",
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      beforeSend: function () {
        $(".main-container").addClass("loading");
      },
      success: function (response) {
        alert("Files uploaded successfully");
        console.log(response);
        $(".main-container").removeClass("loading");
      },
      error: function (e) {
        console.log(e);
        alert("Error uploading files");
      },
    });
  });

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
    var upc = $(this).data("upc");
    var supplier_name = $(this).data("supplier_name");
    $(this).closest("td").find(".clear-btn").show();

    $.ajax({
      url: current_website + "helper/create-orders.php",
      type: "POST",
      data: { upc, supplier_name },
      success: function (response) {
        alert("Item added successfully");
        console.log(response);
      },
      error: function (e) {
        console.log(e);
        alert("Error uploading files");
      },
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
    processing: true, // Show processing indicator
    serverSide: true, // Enable server-side processing
    bLengthChange: false,
    ajax: {
      url: current_website + "helper/fetch-product-data.php",
      type: "GET",
    },
    columns: [
      {
        data: "image",
        render: function (data, type, row) {
          return '<img src="' + data + '" class="product-img"/>';
        },
      },
      { data: "name" },
      { data: "upc" },
      {
        data: "price_supplier",
        render: function (data, type, full, meta) {
          console.log(data);
          var priceSuppliers = data.split(",");
          var html = "";
          var count = 0;
          priceSuppliers.forEach(function (ps) {
            var parts = ps.split(" - ");
            var price = parseFloat(parts[0]).toFixed(2);
            var supplier = parts[1];
            html += `<div class="radio-select-wrapper"><input type="radio" class="radio-select" data-upc="${full.upc}" data-supplier_name="${full.supplier_name}" id="${full.upc}${count}" name="${full.upc}" data-name="${full.name}"> 
            <label for="${full.upc}${count}">${ps} </label></div>
            <br>`;
            count++;
          });
          html += `<button class="clear-btn btn-danger btn" style='display:none'>Clear</button>`;
          return html;
        },
      },
    ],
    paging: true,
    searching: true,
    pageLength: 250,
    initComplete: function (settings, json) {
      // Example: Bind a click event to all radio buttons in the table
      $("#productsTable").on("click", ".radio-select", function () {
        alert("Radio button for " + $(this).data("name") + " clicked!");
      });
    },
  });

  $(".deleteBtn").click(function () {
    var upc = $(this).data("upc");
    var supplier_name = $(this).data("supplier_name");
    if (confirm("Are you sure you want to delete this order?")) {
      $.ajax({
        url: current_website + "helper/server.php",
        type: "POST",
        data: {
          action: "deleteOrder",
          upc: upc,
          supplier_name: supplier_name,
        },
        success: function (response) {
          // Refresh the page to reflect the deletion
          location.reload();
        },
        error: function () {
          alert("Error deleting order.");
        },
      });
    }
  });

  let offset = 0;
  const limit = 40; // Number of images to load each time

  function loadImages() {
    $.ajax({
      url: current_website + "helper/load-images.php", // PHP file to load images
      type: "POST",
      data: {
        offset: offset,
        limit: limit,
      },
      success: function (data) {
        $("#image-gallery").append(data);
        offset += limit;
      },
    });
  }

  //update orders qty
  $(".order-qty-update").on("change", function () {
    var id = $(this).data("id");
    var qty = $(this).val();
    console.log('qty-field');

    $.ajax({
      url: current_website +"helper/server.php", // Adjust to the path of your server file
      type: "POST",
      data: { action: "updateQty", id: id, qty: qty },
      success: function (response) {
        alert("Quantity updated");
      },
      error: function () {
        console.log("Error updating quantity");
      },
    });
  });

  // Load initial set of images
  // loadImages();

  // Load more images on scroll
  // $(window).scroll(function () {
  //   if (
  //     $(window).scrollTop() + $(window).height() >
  //     $(document).height() - 100
  //   ) {
  //     loadImages();
  //   }
  // });
});
