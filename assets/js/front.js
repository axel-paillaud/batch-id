document.addEventListener("DOMContentLoaded", function() {
  const searchInput = document.getElementById("batch-search-input");
  const typeFilter = document.getElementById("batch-filter-type");
  const batchColumns = document.querySelectorAll("[data-batch-id]");

  if (!searchInput || !typeFilter) {
    return;
  }

  function filterBatches() {
    const searchValue = searchInput.value.trim().toLowerCase();
    const selectedType = typeFilter.value.toLowerCase();

    batchColumns.forEach((column) => {
      const batchId = column.dataset.batchId;
      const batchType = column.dataset.batchType.toLowerCase();

      const matchesSearch = batchId.startsWith(searchValue) || searchValue === "";
      const matchesType = batchType === selectedType || selectedType === "";

      if (matchesSearch && matchesType) {
        column.style.display = "block";
      } else {
        column.style.display = "none";
      }
    });
  }

  searchInput.addEventListener("input", filterBatches);
  typeFilter.addEventListener("change", filterBatches);
});
