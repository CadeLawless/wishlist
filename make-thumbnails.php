<?php
/* require("includes/classes.php");
$db = new DB();
ini_set("display_errors", 1);

function rrmdir($dir) {
    if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
          if (filetype($dir."/".$object) == "dir") 
             rrmdir($dir."/".$object); 
          else unlink   ($dir."/".$object);
        }
      }
      reset($objects);
      rmdir($dir);
    }
   }

$findAllWishlists = $db->query("SELECT id FROM wishlists");
if($findAllWishlists->num_rows > 0){
    while($row = $findAllWishlists->fetch_assoc()){
        $wishlistID = $row["id"];
        $findItems = $db->select("SELECT name, image FROM items WHERE wishlist_id = ?", [$wishlistID]);
        if($findItems->num_rows > 0){
            while($item_row = $findItems->fetch_assoc()){
                $item_name = $item_row["name"];
                $item_image = $item_row["image"];
                $item_image_path = "images/item-images/$wishlistID/$item_image";
                $ext_array = explode(".", $item_image);
                end($ext_array);
                $ext = $ext_array[key($ext_array)];
                if(is_dir("images/item-images/$wishlistID/thumbnails")){
                    rrmdir("images/item-images/$wishlistID/thumbnails");
                }
            }
        }
    }
} */
?>