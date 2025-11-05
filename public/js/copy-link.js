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
            // Function to update button UI after successful copy
            const updateButtonUI = () => {
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
            };
            
            // Try modern Clipboard API first (requires HTTPS or localhost)
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(copyText).then(() => {
                    updateButtonUI();
                }).catch(err => {
                    console.error('Clipboard API failed: ', err);
                    // Fallback to execCommand
                    copyTextFallback(copyText, updateButtonUI);
                });
            } else {
                // Fallback for non-secure contexts (HTTP)
                copyTextFallback(copyText, updateButtonUI);
            }
        }
    });
    
    // Fallback copy function using deprecated but widely supported execCommand
    function copyTextFallback(text, callback) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                callback();
            } else {
                alert('Failed to copy link to clipboard');
            }
        } catch (err) {
            console.error('Fallback copy failed: ', err);
            alert('Failed to copy link to clipboard');
        }
        
        document.body.removeChild(textArea);
    }
    
});
