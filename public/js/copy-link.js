/**
 * Copy Link Functionality
 * Handles copying wishlist links to clipboard
 */

$(document).ready(function() {
    
    // Copy link functionality
    $(".copy-link a").on("click", function(e) {
        e.preventDefault();
        
        const button = this;
        const copyText = button.getAttribute('data-copy-text');
        
        if (copyText) {
            navigator.clipboard.writeText(copyText).then(() => {
                // Update button to show "Copied!" state
                const svg = button.querySelector("svg");
                const text = button.querySelector(".copy-link-text");
                
                if (svg && text) {
                    svg.classList.add("hidden");
                    text.textContent = "Copied!";
                    
                    // Reset after 1.3 seconds
                    setTimeout(() => {
                        svg.classList.remove("hidden");
                        text.textContent = "Copy Link to Wish List";
                    }, 1300);
                }
            }).catch(err => {
                console.error('Failed to copy text: ', err);
                alert('Failed to copy link to clipboard');
            });
        }
    });
    
});
