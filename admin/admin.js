// Confirm before deleting a file
function confirmDelete(event, fileName) {
  if (!confirm(`Bạn có chắc chắn muốn xóa file "${fileName}"?`)) {
    event.preventDefault(); // Prevent form submission if user cancels
  }
}

// Highlight the selected file in the list
function highlightSelectedFile(selectedFile) {
  const fileLinks = document.querySelectorAll("ul li a");
  fileLinks.forEach(link => {
    if (link.textContent === selectedFile) {
      link.style.fontWeight = "bold";
      link.style.color = "#2b6cb0";
    } else {
      link.style.fontWeight = "normal";
      link.style.color = "";
    }
  });
}

// Example usage: Call this function with the selected file name
// highlightSelectedFile("example.csv");

function hideEditForm() {
  const editForm = document.getElementById("edit-form");
  if (editForm) {
    editForm.parentElement.style.display = "none"; // Hide the parent edit section
  }
}
