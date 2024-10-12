<?php
// check to see if password has been entered
if(!$logged_in){
    if(isset($_COOKIE["wishlist_session_id"])){
        $session = $_COOKIE["wishlist_session_id"];
        $findSession = $db->select("SELECT username, session_expiration FROM wishlist_users WHERE session = ?", [$session]);
        if($findSession->num_rows > 0){
            while($row = $findSession->fetch_assoc()){
                $session_expiration = $row["session_expiration"];
                if(date("Y-m-d H:i:s") < $session_expiration){
                    $username = $row["username"];
                    $_SESSION["username"] = $username;
                    $logged_in = true;
                    $_SESSION["wishlist_logged_in"] = true;
                }
            }
        }
    }
}
?>