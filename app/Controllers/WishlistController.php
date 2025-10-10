<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\WishlistService;
use App\Services\ValidationService;
use App\Services\FileUploadService;
use App\Services\PaginationService;
use App\Services\ItemCopyService;

class WishlistController extends Controller
{
    private AuthService $authService;
    private WishlistService $wishlistService;
    private ValidationService $validationService;
    private FileUploadService $fileUploadService;
    private PaginationService $paginationService;
    private ItemCopyService $itemCopyService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
        $this->wishlistService = new WishlistService();
        $this->validationService = new ValidationService();
        $this->fileUploadService = new FileUploadService();
        $this->paginationService = new PaginationService();
        $this->itemCopyService = new ItemCopyService();
    }

    public function index(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlists = $this->wishlistService->getUserWishlists($user['username']);
        
        $data = [
            'user' => $user,
            'wishlists' => $wishlists
        ];

        return $this->view('wishlist/index', $data);
    }

    public function wishlists(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlists = $this->wishlistService->getUserWishlists($user['username']);
        
        $data = [
            'user' => $user,
            'wishlists' => $wishlists
        ];

        return $this->view('wishlist/index', $data);
    }

    public function show(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        // Get pagination number
        $pageno = (int) $this->request->get('pageno', 1);
        
        // Get other wishlists for copy functionality
        $otherWishlists = $this->wishlistService->getOtherWishlists($user['username'], $id);
        
        // Get sorting/filter preferences from session
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $_SESSION['wisher_wishlist_id'] = $id;
        $_SESSION['home'] = "/wishlist/{$id}?pageno={$pageno}#paginate-top";
        $_SESSION['type'] = 'wisher';

        $filters = [
            'sort_priority' => $_SESSION['wisher_sort_priority'] ?? '',
            'sort_price' => $_SESSION['wisher_sort_price'] ?? ''
        ];
        
        // Get ALL items first (for total count and filtering)
        $allItems = $this->wishlistService->getWishlistItems($id, []);
        
        // Apply pagination to get only 12 items per page
        $paginatedItems = $this->paginationService->paginate($allItems, $pageno);
        $totalPages = $this->paginationService->getTotalPages($allItems);
        
        $data = [
            'user' => $user,
            'wishlist' => $wishlist,
            'items' => $paginatedItems,
            'all_items' => $allItems, // For total count display
            'other_wishlists' => $otherWishlists,
            'pageno' => $pageno,
            'total_pages' => $totalPages,
            'filters' => $filters,
            'wishlist_id' => $id
        ];

        return $this->view('wishlist/show', $data);
    }

    public function create(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        
        $data = [
            'user' => $user,
            'wishlist_type' => $this->request->input('wishlist_type', ''),
            'wishlist_name' => $this->request->input('wishlist_name', ''),
            'theme_background_id' => $this->request->input('theme_background_id', ''),
            'theme_gift_wrap_id' => $this->request->input('theme_gift_wrap_id', '')
        ];

        return $this->view('wishlist/create', $data);
    }

    public function store(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $data = $this->request->input();
        $errors = $this->validationService->validateWishlist($data);

        if ($this->validationService->hasErrors($errors)) {
            return $this->view('wishlist/create', [
                'user' => $user,
                'wishlist_type' => $data['wishlist_type'] ?? '',
                'wishlist_name' => $data['wishlist_name'] ?? '',
                'theme_background_id' => $data['theme_background_id'] ?? '',
                'theme_gift_wrap_id' => $data['theme_gift_wrap_id'] ?? '',
                'error_msg' => $this->validationService->formatErrorsForDisplay($errors)
            ]);
        }

        $wishlist = $this->wishlistService->createWishlist($user['username'], $data);
        
        if ($wishlist) {
            return $this->redirect("/wishlist/{$wishlist->id}")->withSuccess('Wishlist created successfully!');
        }

        return $this->view('wishlist/create', [
            'user' => $user,
            'wishlist_type' => $data['wishlist_type'] ?? '',
            'wishlist_name' => $data['wishlist_name'] ?? '',
            'theme_background_id' => $data['theme_background_id'] ?? '',
            'theme_gift_wrap_id' => $data['theme_gift_wrap_id'] ?? '',
            'error_msg' => '<div class="submit-error"><strong>Wishlist creation failed:</strong><ul><li>Unable to create wishlist. Please try again.</li></ul></div>'
        ]);
    }

    public function edit(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        $data = [
            'user' => $user,
            'wishlist' => $wishlist,
            'wishlist_name' => $this->request->input('wishlist_name', $wishlist->wishlist_name)
        ];

        return $this->view('wishlist/edit', $data);
    }

    public function update(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        $data = $this->request->input();
        $errors = $this->validationService->validateWishlist($data);

        if ($this->validationService->hasErrors($errors)) {
            return $this->view('wishlist/edit', [
                'user' => $user,
                'wishlist' => $wishlist,
                'wishlist_name' => $data['wishlist_name'] ?? '',
                'error_msg' => $this->validationService->formatErrorsForDisplay($errors)
            ]);
        }

        if ($this->wishlistService->updateWishlistName($id, $data['wishlist_name'])) {
            return $this->redirect("/wishlist/{$id}")->withSuccess('Wishlist updated successfully!');
        }

        return $this->view('wishlist/edit', [
            'user' => $user,
            'wishlist' => $wishlist,
            'wishlist_name' => $data['wishlist_name'] ?? '',
            'error_msg' => '<div class="submit-error"><strong>Update failed:</strong><ul><li>Unable to update wishlist. Please try again.</li></ul></div>'
        ]);
    }

    public function delete(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->deleteWishlist($id)) {
            return $this->redirect('/wishlist')->withSuccess('Wishlist deleted successfully!');
        }

        return $this->redirect('/wishlist')->withError('Unable to delete wishlist. Please try again.');
    }

    public function toggleVisibility(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleVisibility($id)) {
            $message = $wishlist->isPublic() ? 'Wishlist is now hidden.' : 'Wishlist is now public.';
            return $this->redirect("/wishlist/{$id}")->withSuccess($message);
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to update wishlist visibility.');
    }

    public function toggleComplete(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleComplete($id)) {
            $message = $wishlist->isComplete() ? 'Wishlist has been reactivated.' : 'Wishlist has been marked as complete.';
            return $this->redirect("/wishlist/{$id}")->withSuccess($message);
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to update wishlist status.');
    }

    public function rename(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        $name = $this->request->input('wishlist_name');
        $errors = $this->validationService->validateWishlistName($name);

        if ($this->validationService->hasErrors($errors)) {
            return $this->redirect("/wishlist/{$id}")->withError($this->validationService->formatErrorsForDisplay($errors));
        }

        if ($this->wishlistService->updateWishlistName($id, $name)) {
            return $this->redirect("/wishlist/{$id}")->withSuccess('Wishlist renamed successfully!');
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to rename wishlist. Please try again.');
    }

    public function updateTheme(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        $backgroundId = (int) $this->request->input('theme_background_id');
        $giftWrapId = (int) $this->request->input('theme_gift_wrap_id');

        if ($this->wishlistService->updateWishlistTheme($id, $backgroundId, $giftWrapId)) {
            return $this->redirect("/wishlist/{$id}")->withSuccess('Theme updated successfully!');
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to update theme. Please try again.');
    }

    public function copyFrom(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        $fromWishlistId = (int) $this->request->input('other_wishlist_copy_from');
        $itemIds = $this->request->input('item_ids', []);

        if (empty($itemIds)) {
            return $this->redirect("/wishlist/{$id}")->withError('Please select at least one item to copy.');
        }

        $copiedCount = $this->itemCopyService->copyItems($fromWishlistId, $id, $itemIds);

        if ($copiedCount > 0) {
            return $this->redirect("/wishlist/{$id}")->withSuccess("Successfully copied {$copiedCount} item(s) to this wishlist!");
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to copy items. Please try again.');
    }

    public function copyTo(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        $toWishlistId = (int) $this->request->input('other_wishlist_copy_to');
        $itemIds = $this->request->input('item_ids', []);

        if (empty($itemIds)) {
            return $this->redirect("/wishlist/{$id}")->withError('Please select at least one item to copy.');
        }

        $copiedCount = $this->itemCopyService->copyItems($id, $toWishlistId, $itemIds);

        if ($copiedCount > 0) {
            return $this->redirect("/wishlist/{$id}")->withSuccess("Successfully copied {$copiedCount} item(s) to the other wishlist!");
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to copy items. Please try again.');
    }

    public function hide(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleWishlistVisibility($id)) {
            return $this->redirect("/wishlist/{$id}")->withSuccess('Wishlist is now hidden.');
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to hide wishlist. Please try again.');
    }

    public function showPublic(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleWishlistVisibility($id)) {
            return $this->redirect("/wishlist/{$id}")->withSuccess('Wishlist is now public.');
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to make wishlist public. Please try again.');
    }

    public function complete(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleWishlistComplete($id)) {
            return $this->redirect("/wishlist/{$id}")->withSuccess('Wishlist has been marked as complete.');
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to mark wishlist as complete. Please try again.');
    }

    public function reactivate(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleWishlistComplete($id)) {
            return $this->redirect("/wishlist/{$id}")->withSuccess('Wishlist has been reactivated.');
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to reactivate wishlist. Please try again.');
    }

    public function paginateItems(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return new Response('Wishlist not found', 404);
        }

        $page = (int) $this->request->input('new_page', 1);
        $items = $this->wishlistService->getWishlistItems($id);
        $paginatedItems = $this->paginationService->paginate($items, $page);
        $totalPages = $this->paginationService->getTotalPages($items);

        // Generate HTML for items
        $itemsHtml = $this->generateItemsHtml($paginatedItems, $id, $page);
        
        // Generate pagination controls HTML
        $paginationHtml = $this->generatePaginationHtml($page, $totalPages, count($items));
        
        // Combine items and pagination
        $html = $itemsHtml . $paginationHtml;

        return new Response($html);
    }

    public function filterItems(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return new Response('Wishlist not found', 404);
        }

        // Get filter parameters
        $sortPriority = $this->request->input('sort_priority', '');
        $sortPrice = $this->request->input('sort_price', '');
        
        // Validate filter options
        $validOptions = ['', '1', '2'];
        if (!in_array($sortPriority, $validOptions) || !in_array($sortPrice, $validOptions)) {
            return new Response('<strong>Invalid filter. Please try again.</strong>', 400);
        }
        
        // Update session with filter preferences
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $_SESSION['wisher_sort_priority'] = $sortPriority;
        $_SESSION['wisher_sort_price'] = $sortPrice;

        // Get filtered items (reset to page 1 after filtering)
        $items = $this->wishlistService->getWishlistItems($id);
        $paginatedItems = $this->paginationService->paginate($items, 1);
        $totalPages = $this->paginationService->getTotalPages($items);
        
        $html = $this->generateItemsHtml($paginatedItems, $id, 1, $totalPages);

        return new Response($html);
    }

    public function getOtherWishlistItems(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $otherWishlistId = (int) $this->request->input('wishlist_id');
        $copyFrom = $this->request->input('copy_from') === 'Yes';

        $items = $this->itemCopyService->getWishlistItems($otherWishlistId);
        $html = $this->generateItemCheckboxes($items, $otherWishlistId, $copyFrom);

        return new Response($html);
    }

    private function generateItemsHtml(array $items, int $wishlistId, int $page): string
    {
        $html = '';
        
        foreach ($items as $item) {
            $html .= $this->generateItemHtml($item, $wishlistId, $page);
        }
        
        return $html;
    }

    private function generateItemHtml(array $item, int $wishlistId, int $page): string
    {
        $itemName = htmlspecialchars($item['name']);
        $itemNameShort = htmlspecialchars(substr($item['name'], 0, 25));
        if (strlen($item['name']) > 25) $itemNameShort .= '...';
        
        $price = htmlspecialchars($item['price']);
        $quantity = $item['unlimited'] == 'Yes' ? 'Unlimited' : htmlspecialchars($item['quantity']);
        $notes = htmlspecialchars($item['notes']);
        $notesShort = htmlspecialchars(substr($item['notes'], 0, 30));
        if (strlen($item['notes']) > 30) $notesShort .= '...';
        
        $link = htmlspecialchars($item['link']);
        $imagePath = "/wishlist/images/item-images/{$wishlistId}/{$item['image']}?t=" . time();
        $dateAdded = date("n/j/Y g:i A", strtotime($item['date_added']));
        $dateModified = $item['date_modified'] ? date("n/j/Y g:i A", strtotime($item['date_modified'])) : '';
        
        // Priority descriptions
        $priorities = [
            1 => "absolutely needs this item",
            2 => "really wants this item", 
            3 => "It would be cool if they had this item",
            4 => "Eh, they could do without this item"
        ];
        
        $priorityText = $priorities[$item['priority']] ?? '';

        // Use output buffering to capture the PHP includes
        ob_start();
        ?>
        <div class='item-container'>
            <div class='item-image-container image-popup-button'>
                <img class='item-image' src='<?php echo $imagePath; ?>' alt='wishlist item image'>
            </div>
            <div class='item-description'>
                <div class='line'><h3><?php echo $itemNameShort; ?></h3></div>
                <div class='line'><h4>Price: $<?php echo $price; ?></h4></div>
                <div class='line'><h4 class='notes-label'>Quantity Needed:</h4> <?php echo $quantity; ?></div>
                <div class='line'><h4 class='notes-label'>Notes: </h4><span><?php echo $notesShort; ?></span></div>
                <div class='line'><h4 class='notes-label'>Priority: </h4><span>(<?php echo $item['priority']; ?>)</span></div>
                <div class='icon-options item-options wisher-item-options'>
                    <a class='icon-container popup-button' href='#'>
                        <?php require(__DIR__ . '/../../images/site-images/icons/view.php'); ?>
                        <div class='inline-label'>View Details</div>
                    </a>
                    <div class='popup-container hidden'>
                        <div class='popup'>
                            <div class='close-container'>
                                <a href='#' class='close-button'>
                                    <?php require(__DIR__ . '/../../images/site-images/menu-close.php'); ?>
                                </a>
                            </div>
                            <div class='popup-content'>
                                <h2 style='margin-top: 0;'>Item Details</h2>
                                <p><label>Item Name:<br /></label><?php echo $itemName; ?></p>
                                <p><label>Item Price:<br /></label>$<?php echo $price; ?></p>
                                <p><label>Website Link:<br /></label><a target='_blank' href='<?php echo $link; ?>'>View on Website</a></p>
                                <p><label>Notes: </label><br /><?php echo nl2br($notes); ?></p>
                                <p><label>Priority:<br /></label>(<?php echo $item['priority']; ?>) <?php echo $priorityText; ?></p>
                                <p><label>Date Added:<br /></label><?php echo $dateAdded; ?></p>
                                <?php if($dateModified): ?>
                                <p><label>Last Date Modified:</label><br /><?php echo $dateModified; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <a class='icon-container' href='<?php echo $link; ?>' target='_blank'>
                        <?php require(__DIR__ . '/../../images/site-images/icons/link.php'); ?>
                        <div class='inline-label'>Website Link</div>
                    </a>
                    <a class='icon-container' href='/wishlist/edit-item.php?id=<?php echo $item['id']; ?>&pageno=<?php echo $page; ?>'>
                        <?php require(__DIR__ . '/../../images/site-images/icons/edit.php'); ?>
                        <div class='inline-label'>Edit</div>
                    </a>
                    <a class='icon-container popup-button' href='#'>
                        <?php require(__DIR__ . '/../../images/site-images/icons/delete-x.php'); ?>
                        <div class='inline-label'>Delete</div>
                    </a>
                    <div class='popup-container hidden'>
                        <div class='popup'>
                            <div class='close-container'>
                                <a href='#' class='close-button'>
                                    <?php require(__DIR__ . '/../../images/site-images/menu-close.php'); ?>
                                </a>
                            </div>
                            <div class='popup-content'>
                                <label>Are you sure you want to delete this item?</label>
                                <p><?php echo $itemName; ?></p>
                                <div style='margin: 16px 0;' class='center'>
                                    <a class='button secondary no-button' href='#'>No</a>
                                    <a class='button primary' href='/wishlist/delete-item.php?id=<?php echo $item['id']; ?>&pageno=<?php echo $page; ?>'>Yes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class='date-added center'><em>Date Added: <?php echo $dateAdded; ?></em></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function generateItemCheckboxes(array $items, int $wishlistId, bool $copyFrom): string
    {
        $html = '';
        
        foreach ($items as $item) {
            $itemName = htmlspecialchars($item['name']);
            $itemId = $item['id'];
            $checked = $copyFrom ? 'checked' : '';
            
            $html .= "
            <div class='select-item-container'>
                <input type='checkbox' class='option-checkbox' name='item_{$itemId}' value='{$itemId}' {$checked}>
                <label>{$itemName}</label>
            </div>";
        }
        
        return $html;
    }

    private function generatePaginationHtml(int $currentPage, int $totalPages, int $totalItems): string
    {
        if ($totalPages <= 1) {
            return '';
        }

        $startItem = (($currentPage - 1) * 12) + 1;
        $endItem = min($currentPage * 12, $totalItems);

        // Use output buffering to capture the PHP includes
        ob_start();
        ?>
        <div class='center'>
            <div class='paginate-container'>
                <a class='paginate-arrow paginate-first<?php echo $currentPage <= 1 ? ' disabled' : ''; ?>' href='#'>
                    <?php require(__DIR__ . '/../../images/site-images/first.php'); ?>
                </a>
                <a class='paginate-arrow paginate-previous<?php echo $currentPage <= 1 ? ' disabled' : ''; ?>' href='#'>
                    <?php require(__DIR__ . '/../../images/site-images/prev.php'); ?>
                </a>
                <div class='paginate-title'>
                    <span class='page-number'><?php echo $currentPage; ?></span>/<span class='last-page'><?php echo $totalPages; ?></span>
                </div>
                <a class='paginate-arrow paginate-next<?php echo $currentPage >= $totalPages ? ' disabled' : ''; ?>' href='#'>
                    <?php require(__DIR__ . '/../../images/site-images/prev.php'); ?>
                </a>
                <a class='paginate-arrow paginate-last<?php echo $currentPage >= $totalPages ? ' disabled' : ''; ?>' href='#'>
                    <?php require(__DIR__ . '/../../images/site-images/first.php'); ?>
                </a>
            </div>
        </div>
        <div class='count-showing'>Showing <?php echo $startItem; ?>-<?php echo $endItem; ?> of <?php echo $totalItems; ?> items</div>
        <?php
        return ob_get_clean();
    }
}
