<?php
$errors = false;
$errorTitle = "<b>The form could not be submitted due to the following errors:</b>";
$errorList = "";
$tag = errorCheck("tag", "Tag", "Yes", $errors, $errorList);
validOptionCheck($tag, "Tag", $tag_options, $errors, $errorList);
$name = errorCheck("name", "Background Name", "Yes", $errors, $errorList);
$image_name = errorCheck("image_name", "Image Name", "Yes", $errors, $errorList);
$default_gift_wrap_id = errorCheck("default_gift_wrap_id", "Default Gift Wrap ID", "Yes", $errors, $errorList);
?>