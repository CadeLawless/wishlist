<?php
echo "
<table class='admin-center-table'>
    <thead>
        <tr>
            <th class='th_border'>Name</th>
            <th class='th_border'>Username</th>
            <th class='th_border'>Email</th>
            <th class='th_border'>Role</th>
            <th></th>
        </tr>
    </thead>
    <tbody>";
    while($row = $selectQuery->fetch_assoc()){
        $wishlist_user_name = htmlspecialchars($row["name"]);
        $wishlist_user_username = htmlspecialchars($row["username"]);
        $wishlist_user_email = htmlspecialchars($row["email"]);
        $wishlist_user_role = htmlspecialchars($row["role"]);
        echo "
        <tr>
            <td data-label='Name'>$wishlist_user_name</td>
            <td data-label='Username'>$wishlist_user_username</td>
            <td data-label='Email'>";
            echo $wishlist_user_email == "" ? "Not set up yet" : "<a href='mailto: $wishlist_user_email'>$wishlist_user_email</a>";
            echo "</td>
            <td data-label='Role'>$wishlist_user_role</td>
            <td>
                <div class='icon-group'>
                    <a class='icon-container' href='all-wishlists.php?username=$wishlist_user_username'>";
                    require("$image_folder/site-images/icons/wishlist.php");
                    echo "</a>
                    <a class='icon-container' href='edit-user.php?username=$wishlist_user_username&pageno=$pageNumber'>";
                    require("$image_folder/site-images/icons/edit.php");
                    echo "</a>
                </div>
            </td>
        </tr>";
    }
    echo "
    </tbody>
</table>";
?>