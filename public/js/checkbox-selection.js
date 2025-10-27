/**
 * Checkbox Selection Functionality
 * Handles item selection for copying between wishlists
 */

$(document).ready(function() {
    
    // Checkbox selection functionality
    $(document.body).on("click", ".select-item-container", function(e) {
        e.preventDefault();
        
        const container = this;
        const checkbox = container.querySelector("input");
        const allCheckboxes = container.parentElement.querySelectorAll(".option-checkbox > input:not(.check-all, .already-in-list)");
        
        if (checkbox.checked) {
            // Uncheck
            checkbox.checked = false;
            if (checkbox.classList.contains("check-all")) {
                allCheckboxes.forEach(function(cb) {
                    cb.checked = false;
                });
            }
        } else {
            // Check
            checkbox.checked = true;
            if (checkbox.classList.contains("check-all")) {
                allCheckboxes.forEach(function(cb) {
                    cb.checked = true;
                });
            }
        }
        
        // Update "check all" state
        let checkedCount = 0;
        allCheckboxes.forEach(function(cb) {
            if (cb.checked) checkedCount++;
        });
        
        const checkAllBox = container.parentElement.querySelector(".check-all");
        if (checkAllBox) {
            checkAllBox.checked = (checkedCount === allCheckboxes.length);
        }
    });
    
});
