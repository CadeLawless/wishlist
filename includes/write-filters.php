<form class="filter-form center" method="POST" action="">
    <div class="filter-inputs">
        <div class="filter-input">
            <label for="sort-priority">Sort by Priority</label><br>
            <select class="select-filter" id="sort-priority" name="sort_priority">
                <option value="">None</option>
                <option value="1" <?php if($sort_priority == "1") echo "selected"; ?>>Highest to Lowest</option>
                <option value="2" <?php if($sort_priority == "2") echo "selected"; ?>>Lowest to Highest</option>
            </select>
        </div>
        <div class="filter-input">
            <label for="sort-price">Sort by Price</label><br>
            <select class="select-filter" id="sort-price" name="sort_price">
                <option value="">None</option>
                <option value="1" <?php if($sort_price == "1") echo "selected"; ?>>Lowest to Highest</option>
                <option value="2" <?php if($sort_price == "2") echo "selected"; ?>>Highest to Lowest</option>
            </select>
        </div>
    </div>
</form>