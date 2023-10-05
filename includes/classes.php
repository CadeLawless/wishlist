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

    public function getConnection(){
        return $this->connection;
    }

    // parameterized mysqli select statement
    public function select($sql, $types, $values){
        if($selectStatement = $this->connection->prepare($sql)){
            $selectStatement->bind_param($types, ...$values);
            $selectStatement->execute();
            return $selectStatement->get_result();
        }else{
            //echo $db->error;
            return false;
        }
    }

    // parameterized mysqli write (insert, update, delete) statement
    public function write($sql, $types, $values){
        if($writeStatement = $this->connection->prepare($sql)){
            $writeStatement->bind_param($types, ...$values);
            if($writeStatement->execute()){
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
function paginate($type, $db, $query, $itemsPerPage, $pageNumber){
    $offset = ($pageNumber - 1) * $itemsPerPage;
    $selectQuery = $db->query("$query LIMIT $offset, $itemsPerPage");
    if($selectQuery->num_rows > 0){
        if($type == "wisher"){
            echo "<div class='flex items-list'>";
            while($row = $selectQuery->fetch_assoc()){
                $id = $row["id"];
                $name = htmlspecialchars(substr($row["name"], 0, 30));
                if(strlen($row["name"]) > 30) $name .= "...";
                $price = htmlspecialchars($row["price"]);
                $link = htmlspecialchars($row["link"]);
                $image = htmlspecialchars($row["image"]);
                $notes = htmlspecialchars(substr($row["notes"], 0, 70));
                if(strlen($row["notes"]) > 70) $notes .= "...";
                $date_added = htmlspecialchars(date("n/j/Y g:i A", strtotime($row["date_added"])));
                $price_date = htmlspecialchars(date("n/j/y", strtotime($row["date_added"])));
                echo "
                <div class='item-container'>
                    <img class='item-image' src='images/$image' alt='wishlist item image'>
                    <div class='item-description'>
                        <h3>$name</h3>
                        <h4>Price: $$price <span class='price-date'>(as of $price_date)</span></h4>
                        <h4>Notes:</h4>
                        <p class='notes'>$notes</p>
                        <a class='view-button' href='view.php?id=$id'>View Item</a>
                        <a class='link-button' href='$link' target='_blank'>View Item on Website</a>
                        <p class='date-added center'><em>Date Added: $date_added</em></p>
                    </div>
                </div>";
            }
            echo "</div>";
        }else if($type == "buyer"){

        }
    }
}
?>