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
  const editForm = document.querySelector(".edit-form-container");
  if (editForm) {
    editForm.style.display = "none";
    // Remove the 'selected' class from the parent file-item
    const fileItem = editForm.closest('.file-item');
    if (fileItem) {
      fileItem.classList.remove('selected');
    }
  }
}

// Function to load days data from JSON
async function loadDaysData() {
  try {
    const response = await fetch('../days.json');
    if (!response.ok) {
      throw new Error('Could not load days data');
    }
    return await response.json();
  } catch (error) {
    console.error("Error loading days data:", error);
    return null;
  }
}

// Example: Use days data for creating new schedules
document.addEventListener('DOMContentLoaded', async () => {
  const dateInput = document.getElementById('new_date');
  if (dateInput) {
    dateInput.addEventListener('focus', function() {
      // Simple placeholder
      this.setAttribute('placeholder', 'Ex: 25/4/2023');
    });
    
    dateInput.addEventListener('blur', function() {
      this.setAttribute('placeholder', 'dd/mm/yyyy');
    });
  }
});

async function togglePublic(button, filename, isCurrentlyPublic) {
  const action = isCurrentlyPublic ? 'unpublish' : 'publish';
  try {
    // Create form data
    const formData = new FormData();
    formData.append('filename', filename);
    formData.append('action', action);

    const response = await fetch('toggle_public.php', {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      throw new Error(`Server responded with status: ${response.status}`);
    }

    const text = await response.text();
    let result;

    try {
      result = JSON.parse(text);
    } catch (e) {
      console.error("Invalid JSON response:", text);
      throw new Error("Invalid JSON response from server");
    }

    if (result.success) {
      // Update button text and class
      button.textContent = isCurrentlyPublic ? 'Public' : 'Unpublic';
      button.classList.toggle('is-public');

      // Update parent list item class
      const listItem = button.closest('.file-item');
      listItem.classList.toggle('public');
      listItem.classList.toggle('not-public');

      // No alert or confirm needed
      // Optionally, reload to reflect changes everywhere
      // window.location.reload();
    } else {
      // Only show error if failed
      alert('Error: ' + result.message);
    }
  } catch (error) {
    console.error('Error toggling public status:', error);
    alert('Failed to update public status: ' + error.message);
  }
}
