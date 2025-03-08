    </div>
</body>
</html>
<footer>
  <p class="center">&copy; <?php echo date("Y"); ?> Wish List.<br>
  Designed by Cade and Meleah Lawless. All rights reserved.</p>
</footer>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
  $(document).ready(function(){
    $(".dark-mode-link, .light-mode-link").on("click", function(e){
      e.preventDefault();
      $(document.body).toggleClass("dark");

      $dark = $(document.body).hasClass("dark") ? "Yes" : "No";
      $.ajax({
        type: "POST",
        url: "/wishlist1/change-theme",
        data: {
            dark: $dark,
        },
        dataType: "json",  // Make sure you expect a JSON response
        success: function(response) {
            if (response.status === 'success') {
                console.log("Theme updated:", response.dark);
            } else {
                console.error("Error:", response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", error);
        }
      });
    });
  });
</script>