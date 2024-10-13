<?php
$errors = false;
$errorTitle = "<b>The form could not be submitted due to the following errors:</b>";
$errorList = "";
$item_name = errorCheck("name", "Item Name", "Yes", $errors, $errorList);
$price = errorCheck("price", "Item Price", "Yes", $errors, $errorList);
patternCheck("/(?=.*?\d)^(([1-9]\d{0,2}(,\d{3})*)|\d+)?(\.\d{1,2})?$/", $price, $errors, $errorList, "Item Price must match U.S. currency format: 9,999.00");
$unlimited = isset($_POST["unlimited"]) ? "Yes" : "No";
if($unlimited == "Yes"){
    $quantity = 1;
}else{
    $quantity = errorCheck("quantity", "Quantity", "Yes", $errors, $errorList);
    patternCheck("/(?=.*?\d)^(([1-9]\d{0,2}(,\d{3})*)|\d+)?(\.\d{1,2})?$/", $price, $errors, $errorList, "Item Price must match U.S. currency format: 9,999.00");
}
$link = errorCheck("link", "Item URL", "Yes", $errors, $errorList);
$notes = errorCheck("notes", "Not Required");
$priority = errorCheck("priority", "How much do you want this item", "Yes", $errors, $errorList);
validOptionCheck($priority, "How much do you want this item", $priority_options, $errors, $errorList);
?>