<?php
// check to see if password has been entered
if(!$passwordEntered){
    if(isset($_COOKIE["session_id"])){
        $session = $_COOKIE["session_id"];
        $findSession = $db->select("SELECT session_expiration FROM passwords WHERE session = ?", "s", [$session]);
        if($findSession->num_rows > 0){
            while($row = $findSession->fetch_assoc()){
                $session_expiration = $row["session_expiration"];
                if(date("Y-m-d H:i:s") < $session_expiration){
                    $passwordEntered = true;
                    $_SESSION["password_entered"] = true;
                }
            }
        }
    }
}
?>