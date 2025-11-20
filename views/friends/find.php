<?php 
if(isset($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    $allUsers = $friendService->searchForRequests($user['username'], $searchTerm);
}
?>

<div class="center">
    <h1>Add friends to see what others are wishing for.</h1>
    <?php
    $options = [
        'placeholder' => 'Search for people by name or username...',
        'input_id' => 'friends-search',
        'input_class' => 'friends-search-input',
        'container_class' => 'center',
        'search_term' => $searchTerm ?? ''
    ];
    include __DIR__ . '/../components/admin-table-search.php';
    ?>
</div>

<div class="friends-search-results-container">
    <?php if (isset($searchTerm) && $searchTerm !== ''): ?>
        <?php echo \App\Services\FriendRenderService::generateUserSearchResults($allUsers ?? []); ?>
    <?php endif; ?>
</div>

<script src="/public/js/admin-table-search.js"></script>
<script src="/public/js/button-loading.js"></script>
<script src="/public/js/friend-search.js"></script>