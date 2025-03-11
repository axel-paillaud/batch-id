
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
