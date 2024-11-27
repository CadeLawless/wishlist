<?php
echo "
<table class='admin-center-table'>
    <thead>
        <tr>
            <th class='th_border'>ID</th>
            <th class='th_border'>Tag</th>
            <th class='th_border'>Name</th>
            <th class='th_border'>Image</th>
            <th class='th_border'>Default Gift Wrap</th>
            <th></th>
        </tr>
    </thead>
    <tbody>";
    while($row = $selectQuery->fetch_assoc()){
        $background_id = $row["theme_id"];
        $background_tag = htmlspecialchars($row["theme_tag"]);
        $background_name = htmlspecialchars($row["theme_name"]);
        $background_image = htmlspecialchars($row["theme_image"]);
        $background_default_gift_wrap = htmlspecialchars($row["default_gift_wrap"]);
        echo "
        <tr>
            <td data-label='ID'>" . htmlspecialchars($background_id) . "</td>
            <td data-label='Tag'>$background_tag</td>
            <td data-label='Name'>$background_name</td>
            <td data-label='Image'>$background_image</td>
            <td data-label='Default Gift Wrap'>$background_default_gift_wrap</td>
            <td>
                <div class='icon-group'>
                    <a class='icon-container' href='edit-background.php?id=$background_id&pageno=$pageNumber'>";
                    require("$image_folder/site-images/icons/edit.php");
                    echo "</a>
                    <a class='icon-container popup-button' href='#'>";
                    require("$image_folder/site-images/icons/delete-trashcan.php");
                    echo "</a>
                    <div class='popup-container hidden'>
                        <div class='popup'>
                            <div class='close-container'>
                                <a href='#' class='close-button'>";
                                require("$image_folder/site-images/menu-close.php");
                                echo "</a>
                            </div>
                            <div class='popup-content'>
                                <label>Are you sure you want to delete this background?</label>
                                <p>$background_name</p>
                                <div style='margin: 16px 0;' class='center'>
                                    <a class='button secondary no-button' href='#'>No</a>
                                    <a class='button primary' href='delete-background.php?id=$background_id&pageno=$pageNumber'>Yes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>";
    }
    echo "
    </tbody>
</table>";
?>