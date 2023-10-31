<?php
// check to see if password has been entered
if(!$logged_in){
    if(isset($_COOKIE["session_id"])){
        $session = $_COOKIE["session_id"];
        $findSession = $db->select("SELECT session_expiration FROM passwords WHERE session = ?", "s", [$session]);
        if($findSession->num_rows > 0){
            while($row = $findSession->fetch_assoc()){
                $session_expiration = $row["session_expiration"];
                if(date("Y-m-d H:i:s") < $session_expiration){
                    $logged_in = true;
                    $_SESSION["logged_in"] = true;
                }
            }
        }
    }
}
?>