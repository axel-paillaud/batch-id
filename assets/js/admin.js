
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
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $("#customer").val(ui.item.label);
            $("#customer_id").val(ui.item.value);
            return false;
        }
    });
});
