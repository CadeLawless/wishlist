<?php
// Display flash messages
if (isset($flash['success'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['success']) . "</label></p>
            </div>
        </div>
    </div>";
}

if (isset($flash['error'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['error']) . "</label></p>
            </div>
        </div>
    </div>";
}
?>

<h1 class="center">Admin Center</h1>
<div class="sidebar-main">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    <div class="content">
        <h2 style="margin: 0;" class="items-list-title">All Users</h2>
        <div class="admin-center-table-container">
            <table class="admin-center-table">
                <thead>
                    <tr>
                        <th class="th_border">Name</th>
                        <th class="th_border">Username</th>
                        <th class="th_border">Email</th>
                        <th class="th_border">Role</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $userRow): ?>
                    <tr>
                        <td data-label="Name"><?php echo htmlspecialchars($userRow['name']); ?></td>
                        <td data-label="Username"><?php echo htmlspecialchars($userRow['username']); ?></td>
                        <td data-label="Email">
                            <?php if(empty($userRow['email'])): ?>
                                Not set up yet
                            <?php else: ?>
                                <a href="mailto:<?php echo htmlspecialchars($userRow['email']); ?>"><?php echo htmlspecialchars($userRow['email']); ?></a>
                            <?php endif; ?>
                        </td>
                        <td data-label="Role"><?php echo htmlspecialchars($userRow['role']); ?></td>
                        <td>
                            <div class="icon-group">
                                <a class="icon-container" href="/wishlist/admin/wishlists?username=<?php echo urlencode($userRow['username']); ?>">
                                    <?php require("images/site-images/icons/wishlist.php"); ?>
                                </a>
                                <a class="icon-container" href="/wishlist/admin/users/edit?username=<?php echo urlencode($userRow['username']); ?>">
                                    <?php require("images/site-images/icons/edit.php"); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php if($currentPage > 1): ?>
                <a href="?pageno=<?php echo $currentPage - 1; ?>" class="pagination-link">Previous</a>
            <?php endif; ?>
            
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <?php if($i == $currentPage): ?>
                    <span class="pagination-current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?pageno=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if($currentPage < $totalPages): ?>
                <a href="?pageno=<?php echo $currentPage + 1; ?>" class="pagination-link">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
