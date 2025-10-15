<footer>
  <p class="center">&copy; <?php echo date("Y"); ?> Wishlist.<br>
  Designed by Cade and Meleah Lawless. All rights reserved.</p>
</footer>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="public/js/popups.js"></script>
<script>
  $(document).ready(function(){
    let isToggling = false; // Prevent multiple simultaneous requests
    let lastClickTime = 0;
    
    // Remove any existing event listeners to prevent duplicates
    $(document).off("click", ".dark-mode-link, .light-mode-link");
    
    // Use event delegation to handle clicks on toggle links
    $(document).on("click", ".dark-mode-link, .light-mode-link", function(e){
      const now = Date.now();
      
      // Prevent rapid successive clicks (within 500ms)
      if (now - lastClickTime < 500) {
        e.preventDefault();
        e.stopPropagation();
        return;
      }
      
      if (isToggling) {
        e.preventDefault();
        e.stopPropagation();
        return;
      }
      
      lastClickTime = now;
      
      e.preventDefault();
      e.stopPropagation();
      isToggling = true;
      
      // Toggle the dark class immediately for visual feedback
      $(document.body).toggleClass("dark");

      $dark = $(document.body).hasClass("dark") ? "Yes" : "No";
      
      $.ajax({
            type: "POST",
            url: "/wishlist/toggle-dark-mode",
            data: {
                dark: $dark,
            },
            success: function(response) {
                isToggling = false; // Reset flag on success
            },
            error: function(xhr, status, error) {
                // If request fails, revert the visual change
                $(document.body).toggleClass("dark");
                isToggling = false; // Reset flag on error
            }
        });
    });
  });
</script>
