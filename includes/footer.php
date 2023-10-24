<footer>
  <p class="center">&copy; <?php echo date("Y"); ?> Cade's Christmas Wishlist.<br>
  Icons by <a href="https://icons8.com/" target="_blank">Icons8</a>.<br>
  Designed by Cade and Meleah Lawless. All rights reserved.</p>
</footer>
<script>
  for(const video of document.querySelectorAll(".background")){
        video.play();
        video.addEventListener("ended", function(){
            setTimeout(function(){
                video.play();
            }, 5000);
        });
    }
</script>