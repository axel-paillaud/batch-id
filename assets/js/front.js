document.addEventListener("DOMContentLoaded", function() {
  const searchInput = document.getElementById("batch-search-input");
  const typeFilter = document.getElementById("batch-filter-type");
  const batchColumns = document.querySelectorAll("[data-batch-id]");

  if (searchInput && typeFilter) {
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
  }

  // Handle batch claim form submission via AJAX
  const claimForm = document.getElementById("batch-claim-form");
  if (claimForm) {
    claimForm.addEventListener("submit", function(e) {
      e.preventDefault();

      const batchIdInput = document.getElementById("claim_batch_id");
      const messageDiv = document.getElementById("batch-claim-message");
      const submitButton = claimForm.querySelector("button[type='submit']");

      if (!batchIdInput.value || batchIdInput.value.length !== 10) {
        messageDiv.textContent = "Please enter a valid 10-digit Batch ID.";
        messageDiv.className = "batch-message error";
        messageDiv.style.display = "block";
        return;
      }

      // Disable submit button during request
      submitButton.disabled = true;
      submitButton.textContent = "Processing...";

      // Prepare form data
      const formData = new FormData();
      formData.append("action", "batch_id_claim");
      formData.append("claim_batch_id", batchIdInput.value);
      formData.append("nonce", batchIdClaimData.nonce);

      // Send AJAX request
      fetch(batchIdClaimData.ajaxurl, {
        method: "POST",
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          messageDiv.textContent = data.data.message;
          messageDiv.className = "batch-message success";
          messageDiv.style.display = "block";
          batchIdInput.value = "";
          
          // Redirect after 1 second to refresh the page
          setTimeout(() => {
            window.location.href = data.data.redirect_url;
          }, 1000);
        } else {
          messageDiv.textContent = data.data.message;
          messageDiv.className = "batch-message error";
          messageDiv.style.display = "block";
          submitButton.disabled = false;
          submitButton.textContent = "Get Batch ID";
        }
      })
      .catch(error => {
        messageDiv.textContent = "An error occurred. Please try again.";
        messageDiv.className = "batch-message error";
        messageDiv.style.display = "block";
        submitButton.disabled = false;
        submitButton.textContent = "Get Batch ID";
      });
    });
  }
});
