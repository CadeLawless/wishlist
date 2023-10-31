<?php
// includes db and paginate class and checks if logged in
require "inlcudes/setup.php";

// initialize filter variables
$valid_options = ["", "1", "2"];
$sort_priority = $_SESSION["sort_priority"] ?? "1";
$sort_price = $_SESSION["sort_price"] ?? "";

// if filter is changed
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // if filter value is changed, change session value
    $sort_priority = $_POST["sort_priority"];
    $sort_price = $_POST["sort_price"];
    if(in_array($sort_priority, $valid_options)){
        $_SESSION["sort_priority"] = $sort_priority;
    }
    if(in_array($sort_price, $valid_options)){
        $_SESSION["sort_price"] = $sort_price;
    }
    header("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title>Cade's Christmas Wishlist</title>
</head>
<body>
    <div id="body">
        <?php require "includes/background.php"; ?>
        <h1 class="center">Cade's Christmas Wishlist</h1>
        <a class="logout-button" href="logout.php">Log out</a>
        <div id="container">
            <p class="center">
                <a id="add-item" href='add-item.php'>Add Item to Wishlist</a>
            </p>
            <h2 class='center'>All Items</h2>
            <form class="filter-form" method="POST" action="">
                <div class="filter-input">
                    <label for="sort-priority">Sort by Priority</label><br>
                    <select id="sort-priority" name="sort_priority">
                        <option value="">None</option>
                        <option value="1" <?php if($sort_priority == "1") echo "selected"; ?>>Highest to Lowest</option>
                        <option value="2" <?php if($sort_priority == "2") echo "selected"; ?>>Lowest to Highest</option>
                    </select>
                </div>
                <div class="filter-input">
                    <label for="sort-price">Sort by Price</label><br>
                    <select id="sort-price" name="sort_price">
                        <option value="">None</option>
                        <option value="1" <?php if($sort_price == "1") echo "selected"; ?>>Lowest to Highest</option>
                        <option value="2" <?php if($sort_price == "2") echo "selected"; ?>>Highest to Lowest</option>
                    </select>
                </div>
            </form>
            <h2 class='center'>All Items</h2>
            <?php
            $findPriceTotal = $db->query("SELECT SUM(price) AS total_price FROM items");
            if($findPriceTotal->num_rows > 0){
                while($row = $findPriceTotal->fetch_assoc()){
                    $total_price = round($row["total_price"], 2);
                }
            }
            echo "<h3 class='center'>Current Wishlist Total: $$total_price</h3>";
            if(isset($_SESSION["pageno"])){
                $pageno = $_SESSION["pageno"];
            }else{
                $pageno = 1;
            }
            $priority_order = match ($sort_priority) {
                "" => "",
                "1" => "priority ASC, ",
                "2" => "priority DESC, ",
            };
            $price_order = match ($sort_price) {
                "" => "",
                "1" => "price * 1 ASC, ",
                "2" => "price * 1 DESC, ",
            };
            paginate("wisher", $db, "SELECT * FROM items ORDER BY $priority_order$price_order date_added DESC", 12, $pageno);
            ?>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script>
    // open delete popup for specified item on click of delete button
    for(const del of document.querySelectorAll(".delete-button")){
        del.addEventListener("click", function(){
            document.querySelector(".delete-popup-" + del.id).classList.remove("hidden");
        });
    }

    // close popup on click of x or no button
    for(const x of document.querySelectorAll(".close-button")){
        x.addEventListener("click", function(){
            x.parentElement.parentElement.parentElement.classList.add("hidden");
        })
    }
    for(const x of document.querySelectorAll(".no-button")){
        x.addEventListener("click", function(){
            x.parentElement.parentElement.parentElement.parentElement.classList.add("hidden");
        })
    }

    // submit form on filter change
    for(const sel of document.querySelectorAll("select")){
        sel.addEventListener("change", function(){
            document.querySelector("form").submit();
        });
    }
</script>