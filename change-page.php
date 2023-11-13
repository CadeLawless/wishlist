<?php
session_start();
$home = $_SESSION["home"] ?? "";
$pageno = $_GET["pageno"] ?? "";
if($pageno != "" && $home != ""){
    $_SESSION["pageno"] = $pageno;
    header("Location: $home#paginate-top");
}else{
    header("Location: index.php");
}
?>