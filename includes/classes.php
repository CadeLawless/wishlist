<?php
date_default_timezone_set("America/Chicago");
class DB{
    private $connection;
    private $servername = "localhost";
    private $username = "root";
    private $password = "REDACTED";
    private $database = "wishlist";

    public function __construct()
    {
        $this->connection = new mysqli($this->servername, $this->username, $this->password, $this->database);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }
    public function query($query){
        return $this->connection->query($query);
    }

    public function error(){
        return $this->connection->error;
    }

    public function insert_id(){
        return $this->connection->insert_id;
    }

    public function getConnection(){
        return $this->connection;
    }

    // parameterized mysqli select statement
    public function select($sql, $values){
        if($selectStatement = $this->connection->prepare($sql)){
            $selectStatement->execute($values);
            return $selectStatement->get_result();
        }else{
            //echo $db->error;
            return false;
        }
    }

    // parameterized mysqli write (insert, update, delete) statement
    public function write($sql, $values){
        if($writeStatement = $this->connection->prepare($sql)){
            if($writeStatement->execute($values)){
                return true;
            }else{
                //echo $writeStatement->error;
                return false;
            }
        }else{
            return false;
        }
    }
}
function paginate($type, $db, $query, $itemsPerPage, $pageNumber, $username="", $wishlist_id="", $wishlist_key=""){
    require("paginate-sql.php");
    if($selectQuery->num_rows > 0){
        if($type == "wisher")  echo "<div class='center'><h3 class='wishlist-total'>Current Wishlist Total: $$total_price</h3></div>";
        $numberOfItemsOnPage = $selectQuery->num_rows;
        if($type == "wisher"){
            $numberOfItems = $db->select($query, [$wishlist_id, $username])->num_rows;
        }else if($type == "buyer"){
            $numberOfItems = $db->select($query, [$wishlist_id])->num_rows;
        }
        $totalPages = ceil($numberOfItems / $itemsPerPage);
        $home = match($type){
            "wisher" => "view-wishlist.php?id=$wishlist_id&",
            "buyer" => "buyer-view.php?key=$wishlist_key&",
        };
        if($numberOfItems > $numberOfItemsOnPage){
            echo "
            <div class='center'>
                <div class='paginate-container'>
                    <a class='paginate-arrow paginate-first";
                    if($pageNumber <= 1) echo " disabled";
                    echo "' href='#'></a>
                    <a class='paginate-arrow paginate-previous";
                    if($pageNumber <= 1) echo " disabled";
                    echo "' href='#'></a>
                    <div class='paginate-title'><span class='page-number'>$pageNumber</span>/<span class='last-page'>$totalPages</span></div>
                    <a class='paginate-arrow paginate-next";
                    if($pageNumber >= $totalPages) echo " disabled";
                    echo "' href='#'></a>
                    <a class='paginate-arrow paginate-last";
                    if($pageNumber == $totalPages) echo " disabled";
                    echo "'></a>
                </div>
            </div>";
        }
        echo "<div class='items-list main'>";
        require("write-items-list.php");
        echo "
        </div>
        <div class='center'>
            <div class='paginate-container bottom'>";
                if($numberOfItems > $numberOfItemsOnPage){
                    echo "
                    <a class='paginate-arrow paginate-first";
                    if($pageNumber <= 1) echo " disabled";
                    echo "' href='#'></a>
                    <a class='paginate-arrow paginate-previous";
                    if($pageNumber <= 1) echo " disabled";
                    echo "' href='#'></a>
                    <div class='paginate-title'><span class='page-number'>$pageNumber</span>/$totalPages</div>
                    <a class='paginate-arrow paginate-next";
                    if($pageNumber >= $totalPages) echo " disabled";
                    echo "' href='#'></a>
                    <a class='paginate-arrow paginate-last";
                    if($pageNumber == $totalPages) echo " disabled";
                    echo "'></a>";
                }
                echo "
                <div class='paginate-count'>Showing <span class='count-showing'>" . ($offset + 1) . "-" . ($numberOfItemsOnPage+$offset) . "</span> of " . $numberOfItems . " items</div>
            </div>
        </div>";
    }else{
        if($type == "buyer"){
            echo "<div class='center coal'>Looks like all {$_SESSION["name"]} is getting this year is coal. No items added yet.<img src='images/site-images/coal.gif' class='coal-img'></div>";
        }elseif($type == "wisher"){
            echo "
            <a class='item-container add-placeholder' href='add-item.php'>
                <div class='item-image-container'>
                    <img class='item-image' src='images/site-images/default-photo.png' alt='wishlist item image'>
                </div>
                <div class='item-description'></div>
                <div class='add-label'>Add Item</div>
            </a>";
        }
    }
}
?>