<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// gets wishlist id from session and wishlist info from database
$wishlistID = $_GET["id"] ?? false;
require "includes/wishlist-setup.php";

// delete list database
$sql_errors = false;
if($db->write("DELETE FROM wishlists WHERE id = ?", [$wishlistID])){
    if($db->write("DELETE FROM items WHERE wishlist_id = ?", [$wishlistID])){
        function rrmdir($dir) {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach($objects as $object){
                    if ($object != "." && $object != "..") {
                        if(filetype($dir."/".$object) == "dir"){
                            rrmdir($dir."/".$object); 
                        }else{
                            unlink($dir."/".$object);
                        }
                    }
                }
                reset($objects);
                return rmdir($dir);
            }else{
                return true;
            }
        }
        if(rrmdir("images/item-images/$wishlistID")){
            header("Location: view-wishlists.php");
        }else{
            echo "<script>alert('Something went wrong while trying to delete this wishlist')</script>";
            // echo $db->error();
        }
    }else{
        $sql_errors = true;
    }
}else{
    $sql_errors = true;
}
if($sql_errors){
    echo "<script>alert('Something went wrong while trying to delete this wishlist')</script>";
    // echo $db->error();
}
?>