<footer>
  <p class="center">&copy; <?php echo date("Y"); ?> Wishlist.<br>
  Designed by Cade and Meleah Lawless. All rights reserved.</p>
</footer>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="public/js/popups.js"></script>
<script>
  $(document).ready(function(){
    console.log('Footer script loaded, jQuery version:', $.fn.jquery);
    console.log('Dark mode links found:', $(".dark-mode-link, .light-mode-link").length);
    
    let isToggling = false; // Prevent multiple simultaneous requests
    
    $(".dark-mode-link, .light-mode-link").on("click", function(e){
      if (isToggling) {
        console.log('Dark mode toggle already in progress, ignoring click');
        e.preventDefault();
        return;
      }
      
      console.log('Dark mode link clicked!');
      e.preventDefault();
      isToggling = true;
      
      $(document.body).toggleClass("dark");

      $dark = $(document.body).hasClass("dark") ? "Yes" : "No";
      console.log('Sending dark mode value:', $dark);
      
      $.ajax({
            type: "POST",
            url: "/wishlist/toggle-dark-mode",
            data: {
                dark: $dark,
            },
            success: function(response) {
                console.log('Dark mode toggle successful:', response);
                isToggling = false; // Reset flag on success
            },
            error: function(xhr, status, error) {
                console.error('Dark mode toggle failed:', error);
                console.error('Response:', xhr.responseText);
                console.error('Status:', xhr.status);
                isToggling = false; // Reset flag on error
            }
        });
    });
  });
</script>
