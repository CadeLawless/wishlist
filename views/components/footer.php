<footer>
  <p class="center">&copy; <?php echo date("Y"); ?> Wishlist.<br>
  Designed by Cade and Meleah Lawless. All rights reserved.</p>
</footer>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="public/js/popups.js"></script>
<script>
  $(document).ready(function(){
    $(".dark-mode-link, .light-mode-link").on("click", function(e){
      e.preventDefault();
      $(document.body).toggleClass("dark");

      $dark = $(document.body).hasClass("dark") ? "Yes" : "No";
      $.ajax({
            type: "POST",
            url: "/wishlist/toggle-dark-mode",
            data: {
                dark: $dark,
            },
            success: function(response) {
                console.log('Dark mode toggle successful:', response);
            },
            error: function(xhr, status, error) {
                console.error('Dark mode toggle failed:', error);
                console.error('Response:', xhr.responseText);
                console.error('Status:', xhr.status);
            }
        });
    });
  });
</script>
