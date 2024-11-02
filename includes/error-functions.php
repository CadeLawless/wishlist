<?php
function errorCheck($input, $inputName, $required="No", &$errors="", &$error_list=""){
    if(isset($_POST[$input]) && trim($_POST[$input]) != ""){
        return htmlspecialchars_decode(trim($_POST[$input]));
    }else{
        if($required == "Yes"){
            $errors = true;
            $error_list .= "<li>$inputName is a required field. Please fill it out.</li>";
        }
        return "";
    }
}

// function that checks if select and radio inputs are valid, checking if they are in a valid array
function validOptionCheck($input, $inputName, $validArray, &$errors, &$error_list){
    if($input != ""){
        if(!in_array($input, $validArray)){
            $errors = true;
            $error_list .= "<li>Please select a valid option for <em>$inputName</em></li>";
        }
    }
}

function patternCheck($regex, $input, &$errors, &$error_list, $msg) {
    if(!preg_match($regex, $input)){
        $errors = true;
        $error_list .= "<li>$msg</li>";
    }
}

// function that generates random string
function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>