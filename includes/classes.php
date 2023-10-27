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
        $invisibleDivsNeeded = 3 - ($selectQuery->num_rows % 3);
        $numberOfItemsOnPage = $selectQuery->num_rows;
        $numberOfItems = $db->query($query)->num_rows;
        $totalPages = ceil($numberOfItems / $itemsPerPage);
        echo "<div class='flex items-list'>";
        if($numberOfItems > $numberOfItemsOnPage){
            echo "
            <div class='paginate-container' id='top-paginate'>
                <div class='paginate-footer'>
                    <ul class=\"pagination\">
                        <div>
                            <li class='";
                            if($pageNumber <= 1) echo 'disabled';
                            echo "'><a href=\"change-page.php?pageno=1"."\"><img onClick='if(this.parentElement.parentElement.className == \"disabled\") return false;' class='first' src='images/site-images/first.png'></a></li>
                            <li class=";
                            if($pageNumber <= 1) echo 'disabled';
                            echo ">
                                <a href='";
                                if($pageNumber <= 1){echo "#'";} else { echo "change-page.php?pageno=".($pageNumber - 1).""; }
                                echo "'><img onClick='if(this.parentElement.parentElement.className == \"disabled\") return false;' class='prev' src='images/site-images/prev.png'></a>
                            </li>
                        </div>
                        <li style='cursor: default; position: relative; bottom: 5px;'><strong>$pageNumber/$totalPages</strong></li>
                        <div>
                            <li class=";
                            if($pageNumber >= $totalPages) echo "disabled";
                            echo ">
                                <a href='";
                                if($pageNumber >= $totalPages){ echo '#\''; } else { echo "change-page.php?pageno=".($pageNumber + 1).""; }
                                echo "'><img onClick='if(this.parentElement.parentElement.className == \"disabled\") return false;' class='next' src='images/site-images/prev.png'></a>
                            </li>
                            <li class='";
                            if($pageNumber == $totalPages) echo 'disabled';
                            echo "'><a href=\"change-page.php?pageno=$totalPages"."\"><img onClick='if(this.parentElement.parentElement.className == \"disabled\") return false;' class='last' src='images/site-images/first.png'></a></li>
                        </div>
                    </ul>
                </div>
            </div>";
        }
        $priorities = [
            1 => "Cade absolutely needs this item",
            2 => "Cade really wants this item",
            3 => "It would be cool if Cade had this item",
            4 => "Eh, Cade could do without this item"
        ];
        if($type == "wisher"){
            while($row = $selectQuery->fetch_assoc()){
                $id = $row["id"];
                $name = htmlspecialchars(substr($row["name"], 0, 25));
                if(strlen($row["name"]) > 25) $name .= "...";
                $price = htmlspecialchars($row["price"]);
                $link = htmlspecialchars($row["link"]);
                $image = htmlspecialchars($row["image"]);
                $priority = htmlspecialchars($row["priority"]);
                $notes = htmlspecialchars(substr($row["notes"], 0, 30));
                $notes = $notes == "" ? "None" : $notes;
                if(strlen($row["notes"]) > 30) $notes .= "...";
                $date_added = $row["date_modified"] == NULL ? htmlspecialchars(date("n/j/Y g:i A", strtotime($row["date_added"]))) : htmlspecialchars(date("n/j/Y g:i A", strtotime($row["date_modified"])));
                $price_date = $row["date_modified"] == NULL ? htmlspecialchars(date("n/j/Y", strtotime($row["date_added"]))) : htmlspecialchars(date("n/j/Y", strtotime($row["date_modified"])));
                echo "
                <div class='item-container'>
                    <div class='item-image-container'>
                        <img class='item-image' src='images/item-images/$image' alt='wishlist item image'>
                    </div>
                    <div class='item-description'>
                        <h3>$name</h3>
                        <h4>Price: $$price <span class='price-date'>(as of $price_date)</span></h4>
                        <h4 class='notes-label'>Notes: </h4><span>$notes</span><br>
                        <h4 class='notes-label'>Priority: </h4><span>($priority) $priorities[$priority]</span><br>
                        <p class='center'>
                            <a class='view-button' href='view-item.php?id=$id'>View Item</a>
                            <a class='link-button' href='$link' target='_blank'>View Item on Website</a>
                        </p>
                        <p class='center'>
                            <a class='edit-button' href='edit-item.php?id=$id'>Edit Item</a>
                            <a class='delete-button' id='$id'>Delete Item</a>
                            <div class='popup-container delete-popup-$id flex hidden'>
                                <div class='popup flex'>
                                <div class='close-container'>
                                    <img src='images/site-images/close.png' class='close-button'>
                                </div>
                            <div class='center'>
                                        <label>Are you sure you want to delete this item?</label>
                                        <p>";
                                        echo htmlspecialchars($row["name"]);
                                        echo "</p>
                                        <p class='center'><a class='red_button no-button'>No</a><a class='green_button' href='delete-item.php?id=$id?>'>Yes</a></p>
                                    </div>
                                </div>
                            </div>
                        </p>
                        <p class='date-added center'><em>";
                        if($row["date_modified"] == NULL){
                            echo "Date Added";
                        }else{
                            echo "Last Modified";
                        }
                        echo ": $date_added</em></p>
                    </div>
                </div>";
            }
        }else if($type == "buyer"){
            $bow = 1;
            $gift_wrap = 1;
            while($row = $selectQuery->fetch_assoc()){
                $id = $row["id"];
                $name = htmlspecialchars(substr($row["name"], 0, 25));
                if(strlen($row["name"]) > 25) $name .= "...";
                $price = htmlspecialchars($row["price"]);
                $link = htmlspecialchars($row["link"]);
                $image = htmlspecialchars($row["image"]);
                $priority = htmlspecialchars($row["priority"]);
                $notes = htmlspecialchars(substr($row["notes"], 0, 30));
                $notes = $notes == "" ? "None" : $notes;
                $purchased = $row["purchased"] == "Yes" ? true : false;
                if(strlen($row["notes"]) > 30) $notes .= "...";
                $date_added = $row["date_modified"] == NULL ? htmlspecialchars(date("n/j/Y g:i A", strtotime($row["date_added"]))) : htmlspecialchars(date("n/j/Y g:i A", strtotime($row["date_modified"])));
                $price_date = $row["date_modified"] == NULL ? htmlspecialchars(date("n/j/Y", strtotime($row["date_added"]))) : htmlspecialchars(date("n/j/Y", strtotime($row["date_modified"])));
                if($bow == 7) $bow = 1;
                if($gift_wrap == 13) $gift_wrap = 1;
                echo "<div class='item-container' style='";
                if($purchased) echo "padding-top: 0";
                echo "'>";
                    if($purchased){
                        echo "<img src='images/site-images/gift-wrap-$gift_wrap.png' class='gift-wrap' alt='gift wrap'>";
                        $gift_wrap++;
                    }else{
                        echo "<img class='bow' src='images/site-images/bow-$bow.png' alt='bow'>";
                        $bow++;
                    }
                    echo "
                    <div class='item-image-container'>
                        <img class='item-image' src='images/item-images/$image' alt='wishlist item image'>
                    </div>
                    <div class='item-description'>
                        <h3>$name</h3>
                        <h4>Price: $$price <span class='price-date'>(as of $price_date)</span></h4>
                        <h4 class='notes-label'>Notes: </h4><span>$notes</span><br>
                        <h4 class='notes-label'>Priority: </h4><span>($priority) $priorities[$priority]</span><br>";
                        if(!$purchased){
                            echo "
                            <p class='center'>
                                <a class='view-button' href='view-item.php?id=$id'>View Item</a>
                                <a class='link-button' href='$link' target='_blank'>View Item on Website</a>
                            </p>
                            <p class='center'><input class='purchased-button' type='checkbox' id='$id'><label for='$id'> Mark as Purchased</label></p>
                            <div class='popup-container purchased-popup-$id flex hidden'>
                                <div class='popup flex'>
                                    <div class='close-container'>
                                        <img src='images/site-images/close.png' class='close-button'>
                                    </div>
                                    <div class='center'>
                                        <label>Are you sure you want to mark this item as purchased?</label>
                                        <p>";
                                        echo htmlspecialchars($row["name"]);
                                        echo "</p>
                                        <p class='center'><a class='red_button no-button'>No</a><a class='green_button' href='purchase-item.php?id=$id?>'>Yes</a></p>
                                    </div>
                                </div>
                            </div>";
                        }else{
                            echo "
                            <br>
                            <div class='center'>
                                <h4 class='center'>This item has been purchased!</h4>
                                <span class='unmark-msg'>If you need to unmark an item as purchased, reach out to Meleah or Cade.</span>
                            </div>";
                        }
                        echo "<p class='date-added center'><em>";
                        if($row["date_modified"] == NULL){
                            echo "Date Added";
                        }else{
                            echo "Last Modified";
                        }
                        echo ": $date_added</em></p>

                    </div>
                </div>";
            }
        }
        if($selectQuery->num_rows > 3 && $selectQuery->num_rows < 12){
            for($i=0; $i<$invisibleDivsNeeded; $i++){
                if($invisibleDivsNeeded == 3){
                    echo "
                    <div class='item-container invisible hide-3 placeholder-$i'>
                    </div>";
                }elseif($invisibleDivsNeeded == 2){
                    echo "
                    <div class='item-container hide-2 invisible placeholder-$i'>
                    </div>";
                }else{
                    echo "
                    <div class='item-container invisible'>
                    </div>";
                }
            }
        }
        $numberOfItemsOnPage = $selectQuery->num_rows;
        $numberOfItems = $db->query($query)->num_rows;
        $totalPages = ceil($numberOfItems / $itemsPerPage);
        echo "<div class='paginate-container'><div class='paginate-footer'>";
        if($numberOfItems > $numberOfItemsOnPage){
            echo "
            <ul class=\"pagination\">
                <div>
                    <li class='";
                    if($pageNumber <= 1) echo 'disabled';
                    echo "'><a href=\"change-page.php?pageno=1"."\"><img onClick='if(this.parentElement.parentElement.className == \"disabled\") return false;' class='first' src='images/site-images/first.png'></a></li>
                    <li class=";
                    if($pageNumber <= 1) echo 'disabled';
                    echo ">
                        <a href='";
                        if($pageNumber <= 1){echo "#'";} else { echo "change-page.php?pageno=".($pageNumber - 1).""; }
                        echo "'><img onClick='if(this.parentElement.parentElement.className == \"disabled\") return false;' class='prev' src='images/site-images/prev.png'></a>
                    </li>
                </div>
                <li style='font-size: 14px; cursor: default; position: relative; bottom: 5px;'><strong style='font-size: 33px'>$pageNumber/$totalPages</strong></li>
                <div>
                    <li class=";
                    if($pageNumber >= $totalPages) echo "disabled";
                    echo ">
                        <a href='";
                        if($pageNumber >= $totalPages){ echo '#\''; } else { echo "change-page.php?pageno=".($pageNumber + 1).""; }
                        echo "'><img onClick='if(this.parentElement.parentElement.className == \"disabled\") return false;' class='next' src='images/site-images/prev.png'></a>
                    </li>
                    <li class='";
                    if($pageNumber == $totalPages) echo 'disabled';
                    echo "'><a href=\"change-page.php?pageno=$totalPages"."\"><img onClick='if(this.parentElement.parentElement.className == \"disabled\") return false;' class='last' src='images/site-images/first.png'></a></li>
                </div>
            </ul>";
        }
        echo "
            <div id='item-count'>
                <p>Showing " . ($offset + 1) . "-" . ($numberOfItemsOnPage+$offset) . " of " . $numberOfItems . " items</p>
            </div>
        </div>
        </div>";
    }
}
?>