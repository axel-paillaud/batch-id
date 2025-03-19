document.addEventListener("DOMContentLoaded", function() {
  const searchInput = document.getElementById("batch-search-input");
  const batchColumns = document.querySelectorAll("[data-batch-id]");

  searchInput.addEventListener("input", (e) => {
    const searchValue = e.target.value.trim().toLowerCase();

    batchColumns.forEach((column) => {
      const batchId = column.dataset.batchId;
      if (batchId.startsWith(searchValue) || searchValue === "") {
        column.style.display = "block";
      } else {
        column.style.display = "none";
      }
    });
  });
});
