<?php
date_default_timezone_set("America/Chicago");
$dark = false;
class DB{
    private $connection;
    private $servername;
    private $username;
    private $password;
    private $database;

    public function __construct()
    {
        // Load environment variables
        if (file_exists('.env')) {
            $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    [$key, $value] = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
        
        // Set database connection parameters from environment variables
        $this->servername = $_ENV['DB_HOST'] ?? 'localhost';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
        $this->database = $_ENV['DB_NAME'] ?? 'wishlist';
        
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
function paginate(string $type, DB $db, string $query, int $itemsPerPage, int $pageNumber, array $values, string $username="", string $wishlist_id="", string $wishlist_key=""){
    global $ajax;
    require("paginate-sql.php");
    if($selectQuery->num_rows > 0){
        if($type == "wisher")  echo "<div class='center'><h3 class='wishlist-total'>Current Wish List Total: $$total_price</h3></div>";
        $numberOfItemsOnPage = $selectQuery->num_rows;
        $numberOfItems = $db->select($query, $values)->num_rows;
        $totalPages = ceil($numberOfItems / $itemsPerPage);
        $home = match($type){
            "wisher" => "view-wishlist.php?id=$wishlist_id&",
            "buyer" => "buyer-view.php?key=$wishlist_key&",
            "users" => "admin-center.php",
            "backgrounds" => "backgrounds.php",
            default => "",
        };

        $row_label = match($type){
            "buyer", "wisher" => "items",
            "users" => "users",
            "backgrounds" => "backgrounds",
            default => "things",
        };

        if(in_array($type, ["wisher", "buyer"])){
            if($numberOfItems > $numberOfItemsOnPage){
                echo "
                <div class='center'>
                    <div class='paginate-container'>
                        <a class='paginate-arrow paginate-first";
                        if($pageNumber <= 1) echo " disabled";
                        echo "' href='#'>";
                        require("$image_folder/site-images/first.php");
                        echo "</a>
                        <a class='paginate-arrow paginate-previous";
                        if($pageNumber <= 1) echo " disabled";
                        echo "' href='#'>";
                        require("$image_folder/site-images/prev.php");
                        echo "</a>
                        <div class='paginate-title'><span class='page-number'>$pageNumber</span>/<span class='last-page'>$totalPages</span></div>
                        <a class='paginate-arrow paginate-next";
                        if($pageNumber >= $totalPages) echo " disabled";
                        echo "' href='#'>";
                        require("$image_folder/site-images/prev.php");
                        echo "</a>
                        <a class='paginate-arrow paginate-last";
                        if($pageNumber == $totalPages) echo " disabled";
                        echo "' href='#'>";
                        require("$image_folder/site-images/first.php");
                        echo "</a>
                    </div>
                </div>";
            }
            echo "<div class='items-list main'>";
            require("write-items-list.php");
            echo "
            </div>";
        }else{
            echo "<div class='results-table'>";
            if($type == "users"){
                require("write-users-table.php");
            }elseif($type == "backgrounds"){
                require("write-backgrounds-table.php");
            }
            echo "</div>";
        }
        echo "<div class='paginate-container-div center'>";
            if(in_array($type, ["users", "backgrounds"])){
                echo "
                <div class='paginate-count'>Showing <span class='count-showing'>" . ($offset + 1) . "-" . ($numberOfItemsOnPage+$offset) . "</span> of " . $numberOfItems . " $row_label</div>";
            }
            echo "<div class='paginate-container bottom'>";
                if($numberOfItems > $numberOfItemsOnPage){
                    echo "
                    <a class='paginate-arrow paginate-first";
                    if($pageNumber <= 1) echo " disabled";
                    echo "' href='#'>";
                    require("$image_folder/site-images/first.php");
                    echo "</a>
                    <a class='paginate-arrow paginate-previous";
                    if($pageNumber <= 1) echo " disabled";
                    echo "' href='#'>";
                    require("$image_folder/site-images/prev.php");
                    echo "</a>
                    <div class='paginate-title'><span class='page-number'>$pageNumber</span>/<span class='last-page'>$totalPages</span></div>
                    <a class='paginate-arrow paginate-next";
                    if($pageNumber >= $totalPages) echo " disabled";
                    echo "' href='#'>";
                    require("$image_folder/site-images/prev.php");
                    echo "</a>
                    <a class='paginate-arrow paginate-last";
                    if($pageNumber == $totalPages) echo " disabled";
                    echo "' href='#'>";
                    require("$image_folder/site-images/first.php");
                    echo "</a>";
                }
                if(in_array($type, ["wisher", "buyer"])){
                    echo "
                    <div class='paginate-count'>Showing <span class='count-showing'>" . ($offset + 1) . "-" . ($numberOfItemsOnPage+$offset) . "</span> of " . $numberOfItems . " $row_label</div>";
                }
                echo "
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
                <div class='add-label'>";
                require("images/site-images/icons/plus.php");
                echo "Add Item</div>
            </a>";
        }
    }
}
?>