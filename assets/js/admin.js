document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".toggle-barcodes").forEach(button => {
        button.addEventListener("click", function() {
            let batchId = this.getAttribute("data-batch");
            let barcodeList = document.querySelector(".barcodes-list[data-batch='" + batchId + "']");
            if (barcodeList.style.display === "none") {
                barcodeList.style.display = "block";
                this.textContent = "Masquer les barcodes";
            } else {
                barcodeList.style.display = "none";
                this.textContent = "Voir les barcodes";
            }
        });
    });
});

// Autocomplete for customer field
jQuery(document).ready(function($) {
  let autocompleteOpen = false;

  $("#customer").autocomplete({
    source: function(request, response) {
      $.ajax({
        url: ajaxurl, // URL WP's admin-ajax.php
        dataType: "json",
        data: {
          action: "batch_id_search_customers",
          term: request.term
        },
        success: function(data) {
          response(data);
          autocompleteOpen = data.length > 0;
        }
      });
    },
    minLength: 2,
    select: function(event, ui) {
      $("#customer").val(ui.item.label);
      $("#customer_id").val(ui.item.value);
      autocompleteOpen = false;
      return false;
    },
    focus: function(event, ui) {
        $("#customer").val(ui.item.label);
        return false;
    }
  });

  // If the autocomplete is open and the user presses Enter,
  // select the first item in the autocomplete list before sending the form
  $("#customer").keydown(function(event) {
      if (event.key === "Enter" && autocompleteOpen) {
          event.preventDefault();

          let menu = $("#customer").autocomplete("widget");
          let firstItem = menu.find("li:first");

          if (firstItem.length) {
              firstItem.trigger("click");
              $("#customer").blur().focus();
          }
      }
  });
});

function showBatchIdMessage(message, type) {
    let msgDiv = $("#batch-id-message");
    msgDiv.text(message)
        .removeClass("success error")
        .addClass(type)
        .fadeIn();

    setTimeout(function() {
        msgDiv.fadeOut();
    }, 4000);
}
