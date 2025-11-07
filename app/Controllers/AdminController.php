<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Constants;
use App\Models\User;
use App\Models\Theme;
use App\Models\Wishlist;
use App\Core\Database;
use App\Services\PaginationService;
use App\Services\AdminRenderService;
use App\Services\WishlistService;
use App\Services\SessionManager;
use App\Services\FilterService;
use App\Services\FileUploadService;

class AdminController extends Controller
{
    private PaginationService $paginationService;
    private FileUploadService $fileUploadService;

    public function __construct()
    {
        parent::__construct();
        $this->paginationService = new PaginationService(Constants::ADMIN_ITEMS_PER_PAGE);
        $this->fileUploadService = new FileUploadService();
    }

    public function users(): Response
    {
        $user = $this->auth();
        
        // Get all users first (for pagination)
        $allUsers = User::all();
        $page = (int)($this->request->get('pageno', 1));
        
        // Apply pagination
        $users = $this->paginationService->paginate($allUsers, $page);
        $totalPages = $this->paginationService->getTotalPages();
        $correctedPage = $this->paginationService->getCurrentPage();
        $totalUsers = count($allUsers);
        
        $data = [
            'user' => $user,
            'users' => $users,
            'all_users' => $allUsers,
            'currentPage' => $correctedPage,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'currentPageUrl' => '/admin/users'
        ];
        
        return $this->view('admin/users', $data);
    }

    public function backgrounds(): Response
    {
        $user = $this->auth();
        
        // Get all backgrounds first (for pagination)
        $allBackgrounds = Theme::getThemesByType('Background');
        $page = (int)($this->request->get('pageno', 1));
        
        // Apply pagination
        $backgrounds = $this->paginationService->paginate($allBackgrounds, $page);
        $totalPages = $this->paginationService->getTotalPages();
        $correctedPage = $this->paginationService->getCurrentPage();
        $totalBackgrounds = count($allBackgrounds);
        
        $data = [
            'user' => $user,
            'backgrounds' => $backgrounds,
            'all_backgrounds' => $allBackgrounds,
            'currentPage' => $correctedPage,
            'totalPages' => $totalPages,
            'totalBackgrounds' => $totalBackgrounds,
            'currentPageUrl' => '/admin/backgrounds'
        ];
        
        return $this->view('admin/backgrounds', $data);
    }

    public function giftWraps(): Response
    {
        $user = $this->auth();
        
        // Get all gift wraps first (for pagination)
        $allGiftWraps = Theme::getThemesByType('Gift Wrap');
        $page = (int)($this->request->get('pageno', 1));
        
        // Apply pagination
        $giftWraps = $this->paginationService->paginate($allGiftWraps, $page);
        $totalPages = $this->paginationService->getTotalPages();
        $correctedPage = $this->paginationService->getCurrentPage();
        $totalGiftWraps = count($allGiftWraps);
        
        $data = [
            'user' => $user,
            'giftWraps' => $giftWraps,
            'all_gift_wraps' => $allGiftWraps,
            'currentPage' => $correctedPage,
            'totalPages' => $totalPages,
            'totalGiftWraps' => $totalGiftWraps,
            'currentPageUrl' => '/admin/gift-wraps'
        ];
        
        return $this->view('admin/gift-wraps', $data);
    }

    public function wishlists(): Response
    {
        $user = $this->auth();
        
        // Get all wishlists with user info first (for pagination)
        $stmt = Database::query(
            "SELECT w.*, u.name as user_name, u.username 
             FROM wishlists w 
             JOIN wishlist_users u ON w.username = u.username 
             ORDER BY w.id DESC"
        );
        $allWishlists = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $page = (int)($this->request->get('pageno', 1));
        
        // Apply pagination
        $wishlists = $this->paginationService->paginate($allWishlists, $page);
        $totalPages = $this->paginationService->getTotalPages();
        $correctedPage = $this->paginationService->getCurrentPage();
        $totalWishlists = count($allWishlists);
        
        $data = [
            'user' => $user,
            'wishlists' => $wishlists,
            'all_wishlists' => $allWishlists,
            'currentPage' => $correctedPage,
            'totalPages' => $totalPages,
            'totalWishlists' => $totalWishlists,
            'currentPageUrl' => '/admin/wishlists'
        ];
        
        return $this->view('admin/wishlists', $data);
    }

    public function paginateUsers(): void
    {
        $user = $this->auth();
        
        $page = (int) $this->request->input('new_page', 1);
        
        // Get all users
        $allUsers = User::all();
        $paginatedUsers = $this->paginationService->paginate($allUsers, $page);
        $totalPages = $this->paginationService->getTotalPages();
        $totalRows = count($allUsers);
        
        // Generate HTML for table rows only
        $tableHtml = AdminRenderService::generateUsersTableHtml($paginatedUsers);
        
        // Calculate pagination info
        $itemsPerPage = Constants::ADMIN_ITEMS_PER_PAGE;
        $paginationInfoStart = (($page - 1) * $itemsPerPage) + 1;
        $paginationInfoEnd = min($page * $itemsPerPage, $totalRows);
        $paginationInfo = "Showing {$paginationInfoStart}-{$paginationInfoEnd} of {$totalRows} users";
        
        // Clear any output buffering first
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers and output JSON directly
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        $jsonData = [
            'status' => 'success',
            'message' => 'Users loaded successfully',
            'html' => $tableHtml,
            'current' => $page,
            'total' => $totalPages,
            'paginationInfo' => $paginationInfo
        ];
        
        echo json_encode($jsonData);
        flush();
        exit;
    }

    public function paginateBackgrounds(): void
    {
        $user = $this->auth();
        
        $page = (int) $this->request->input('new_page', 1);
        
        // Get all backgrounds
        $allBackgrounds = Theme::getThemesByType('Background');
        $paginatedBackgrounds = $this->paginationService->paginate($allBackgrounds, $page);
        $totalPages = $this->paginationService->getTotalPages();
        $totalRows = count($allBackgrounds);
        
        // Generate HTML for table rows only
        $tableHtml = AdminRenderService::generateBackgroundsTableHtml($paginatedBackgrounds, $page);
        
        // Calculate pagination info
        $itemsPerPage = Constants::ADMIN_ITEMS_PER_PAGE;
        $paginationInfoStart = (($page - 1) * $itemsPerPage) + 1;
        $paginationInfoEnd = min($page * $itemsPerPage, $totalRows);
        $paginationInfo = "Showing {$paginationInfoStart}-{$paginationInfoEnd} of {$totalRows} backgrounds";
        
        // Clear any output buffering first
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers and output JSON directly
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        $jsonData = [
            'status' => 'success',
            'message' => 'Backgrounds loaded successfully',
            'html' => $tableHtml,
            'current' => $page,
            'total' => $totalPages,
            'paginationInfo' => $paginationInfo
        ];
        
        echo json_encode($jsonData);
        flush();
        exit;
    }

    public function paginateGiftWraps(): void
    {
        $user = $this->auth();
        
        $page = (int) $this->request->input('new_page', 1);
        
        // Get all gift wraps
        $allGiftWraps = Theme::getThemesByType('Gift Wrap');
        $paginatedGiftWraps = $this->paginationService->paginate($allGiftWraps, $page);
        $totalPages = $this->paginationService->getTotalPages();
        $totalRows = count($allGiftWraps);
        
        // Generate HTML for table rows only
        $tableHtml = AdminRenderService::generateGiftWrapsTableHtml($paginatedGiftWraps, $page);
        
        // Calculate pagination info
        $itemsPerPage = Constants::ADMIN_ITEMS_PER_PAGE;
        $paginationInfoStart = (($page - 1) * $itemsPerPage) + 1;
        $paginationInfoEnd = min($page * $itemsPerPage, $totalRows);
        $paginationInfo = "Showing {$paginationInfoStart}-{$paginationInfoEnd} of {$totalRows} gift wraps";
        
        // Clear any output buffering first
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers and output JSON directly
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        $jsonData = [
            'status' => 'success',
            'message' => 'Gift wraps loaded successfully',
            'html' => $tableHtml,
            'current' => $page,
            'total' => $totalPages,
            'paginationInfo' => $paginationInfo
        ];
        
        echo json_encode($jsonData);
        flush();
        exit;
    }

    public function paginateWishlists(): void
    {
        $user = $this->auth();
        
        $page = (int) $this->request->input('new_page', 1);
        
        // Get all wishlists with user info
        $stmt = Database::query(
            "SELECT w.*, u.name as user_name, u.username 
             FROM wishlists w 
             JOIN wishlist_users u ON w.username = u.username 
             ORDER BY w.id DESC"
        );
        $allWishlists = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $paginatedWishlists = $this->paginationService->paginate($allWishlists, $page);
        $totalPages = $this->paginationService->getTotalPages();
        $totalRows = count($allWishlists);
        
        // Generate HTML for table rows only
        $tableHtml = AdminRenderService::generateWishlistsTableHtml($paginatedWishlists, $page);
        
        // Calculate pagination info
        $itemsPerPage = Constants::ADMIN_ITEMS_PER_PAGE;
        $paginationInfoStart = (($page - 1) * $itemsPerPage) + 1;
        $paginationInfoEnd = min($page * $itemsPerPage, $totalRows);
        $paginationInfo = "Showing {$paginationInfoStart}-{$paginationInfoEnd} of {$totalRows} wishlists";
        
        // Clear any output buffering first
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers and output JSON directly
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        $jsonData = [
            'status' => 'success',
            'message' => 'Wishlists loaded successfully',
            'html' => $tableHtml,
            'current' => $page,
            'total' => $totalPages,
            'paginationInfo' => $paginationInfo
        ];
        
        echo json_encode($jsonData);
        flush();
        exit;
    }

    public function editBackground(): Response
    {
        $user = $this->auth();
        
        $id = (int) $this->request->get('id');
        if (!$id) {
            return $this->redirect('/admin/backgrounds')->withError('Invalid background ID.');
        }
        
        $background = Theme::find($id);
        if (!$background || $background['theme_type'] !== 'Background') {
            return $this->redirect('/admin/backgrounds')->withError('Background not found.');
        }
        
        $pageno = (int) $this->request->get('pageno', 1);
        
        // Check for form data in session (from validation errors)
        $sessionFormData = \App\Services\SessionManager::get('admin_background_edit_form_data');
        if (\App\Services\SessionManager::has('admin_background_edit_form_data')) {
            \App\Services\SessionManager::remove('admin_background_edit_form_data');
        }
        
        $data = [
            'user' => $user,
            'background' => $background,
            'pageno' => $pageno,
            'currentPageUrl' => '/admin/backgrounds',
            'theme_name' => $sessionFormData['theme_name'] ?? null,
            'theme_tag' => $sessionFormData['theme_tag'] ?? null,
            'theme_image' => $sessionFormData['theme_image'] ?? null,
            'default_gift_wrap' => $sessionFormData['default_gift_wrap'] ?? null
        ];
        
        return $this->view('admin/backgrounds/edit', $data);
    }

    public function updateBackground(): Response
    {
        $user = $this->auth();
        
        $id = (int) $this->request->input('theme_id');
        $pageno = (int) $this->request->input('pageno', 1);
        
        if (!$id) {
            return $this->redirect('/admin/backgrounds')->withError('Invalid background ID.');
        }
        
        $background = Theme::find($id);
        if (!$background || $background['theme_type'] !== 'Background') {
            return $this->redirect('/admin/backgrounds')->withError('Background not found.');
        }
        
        $theme_name = trim($this->request->input('theme_name', ''));
        $theme_tag = trim($this->request->input('theme_tag', ''));
        $theme_image = trim($this->request->input('theme_image', ''));
        $default_gift_wrap = (int) $this->request->input('default_gift_wrap', 0);
        
        // Validation
        $errors = [];
        if (empty($theme_name)) {
            $errors[] = 'Theme name is required.';
        }
        
        if (empty($theme_tag) || !in_array($theme_tag, ['birthday', 'christmas'])) {
            $errors[] = 'Valid theme tag is required.';
        }
        
        if (empty($theme_image)) {
            $errors[] = 'Theme image is required.';
        }
        
        if (!empty($errors)) {
            // Store form data in session for POST-Redirect-GET pattern
            \App\Services\SessionManager::set('admin_background_edit_form_data', [
                'theme_name' => $theme_name,
                'theme_tag' => $theme_tag,
                'theme_image' => $theme_image,
                'default_gift_wrap' => $default_gift_wrap
            ]);
            return $this->redirect("/admin/backgrounds/edit?id={$id}&pageno={$pageno}")->withError(implode(' ', $errors));
        }
        
        // Handle image uploads
        // Determine image name and extension - use uploaded desktop background extension if provided, otherwise keep existing
        $imageNameBase = pathinfo($theme_image, PATHINFO_FILENAME);
        $imageExtension = pathinfo($theme_image, PATHINFO_EXTENSION);
        $finalImageName = $theme_image; // Default to what user entered
        
        // Upload desktop background
        if ($this->request->hasFile('desktop_background')) {
            $file = $this->request->file('desktop_background');
            $uploadedExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Update extension if we have a new upload
            if (!empty($uploadedExtension)) {
                $imageExtension = $uploadedExtension;
                $finalImageName = $imageNameBase . '.' . $uploadedExtension;
            }
            
            $uploadResult = $this->fileUploadService->uploadBackgroundImage(
                $file,
                $imageNameBase,
                'desktop'
            );
            
            if (!$uploadResult['success']) {
                \App\Services\SessionManager::set('admin_background_edit_form_data', [
                    'theme_name' => $theme_name,
                    'theme_tag' => $theme_tag,
                    'theme_image' => $theme_image,
                    'default_gift_wrap' => $default_gift_wrap
                ]);
                return $this->redirect("/admin/backgrounds/edit?id={$id}&pageno={$pageno}")->withError('Desktop background upload failed: ' . $uploadResult['error']);
            }
            
            // Update final image name to match uploaded filename
            $finalImageName = $uploadResult['filename'];
            
            // Auto-generate desktop thumbnail if not provided
            if (!$this->request->hasFile('desktop_thumbnail')) {
                $thumbnailResult = $this->fileUploadService->createBackgroundThumbnail(
                    $uploadResult['filename'],
                    $imageNameBase,
                    'desktop'
                );
                if (!$thumbnailResult['success']) {
                    error_log('Failed to auto-generate desktop thumbnail: ' . $thumbnailResult['error']);
                }
            }
        }
        
        // Upload desktop thumbnail (if provided)
        if ($this->request->hasFile('desktop_thumbnail')) {
            $thumbnailResult = $this->fileUploadService->uploadBackgroundThumbnail(
                $this->request->file('desktop_thumbnail'),
                $imageNameBase,
                'desktop'
            );
            
            if (!$thumbnailResult['success']) {
                error_log('Failed to upload desktop thumbnail: ' . $thumbnailResult['error']);
            }
        }
        
        // Upload mobile background
        if ($this->request->hasFile('mobile_background')) {
            $file = $this->request->file('mobile_background');
            $uploadResult = $this->fileUploadService->uploadBackgroundImage(
                $file,
                $imageNameBase,
                'mobile'
            );
            
            if (!$uploadResult['success']) {
                \App\Services\SessionManager::set('admin_background_edit_form_data', [
                    'theme_name' => $theme_name,
                    'theme_tag' => $theme_tag,
                    'theme_image' => $theme_image,
                    'default_gift_wrap' => $default_gift_wrap
                ]);
                return $this->redirect("/admin/backgrounds/edit?id={$id}&pageno={$pageno}")->withError('Mobile background upload failed: ' . $uploadResult['error']);
            }
            
            // Auto-generate mobile thumbnail if not provided
            if (!$this->request->hasFile('mobile_thumbnail')) {
                $thumbnailResult = $this->fileUploadService->createBackgroundThumbnail(
                    $uploadResult['filename'],
                    $imageNameBase,
                    'mobile'
                );
                if (!$thumbnailResult['success']) {
                    error_log('Failed to auto-generate mobile thumbnail: ' . $thumbnailResult['error']);
                }
            }
        }
        
        // Upload mobile thumbnail (if provided)
        if ($this->request->hasFile('mobile_thumbnail')) {
            $thumbnailResult = $this->fileUploadService->uploadBackgroundThumbnail(
                $this->request->file('mobile_thumbnail'),
                $imageNameBase,
                'mobile'
            );
            
            if (!$thumbnailResult['success']) {
                error_log('Failed to upload mobile thumbnail: ' . $thumbnailResult['error']);
            }
        }
        
        // Update the background - use finalImageName if it was updated by uploads
        $updateData = [
            'theme_name' => $theme_name,
            'theme_tag' => $theme_tag,
            'theme_image' => $finalImageName,
            'default_gift_wrap' => $default_gift_wrap
        ];
        
        if (Theme::update($id, $updateData)) {
            // Clear session form data on success
            \App\Services\SessionManager::remove('admin_background_edit_form_data');
            return $this->redirect("/admin/backgrounds?pageno={$pageno}")->withSuccess('Background updated successfully.');
        }
        
        // Store form data in session for error
        \App\Services\SessionManager::set('admin_background_edit_form_data', [
            'theme_name' => $theme_name,
            'theme_tag' => $theme_tag,
            'theme_image' => $theme_image,
            'default_gift_wrap' => $default_gift_wrap
        ]);
        return $this->redirect("/admin/backgrounds/edit?id={$id}&pageno={$pageno}")->withError('Failed to update background. Please try again.');
    }

    public function editGiftWrap(): Response
    {
        $user = $this->auth();
        
        $id = (int) $this->request->get('id');
        if (!$id) {
            return $this->redirect('/admin/gift-wraps')->withError('Invalid gift wrap ID.');
        }
        
        $giftWrap = Theme::find($id);
        if (!$giftWrap || $giftWrap['theme_type'] !== 'Gift Wrap') {
            return $this->redirect('/admin/gift-wraps')->withError('Gift wrap not found.');
        }
        
        $pageno = (int) $this->request->get('pageno', 1);
        
        // Check for form data in session (from validation errors)
        $sessionFormData = \App\Services\SessionManager::get('admin_giftwrap_edit_form_data');
        if (\App\Services\SessionManager::has('admin_giftwrap_edit_form_data')) {
            \App\Services\SessionManager::remove('admin_giftwrap_edit_form_data');
        }
        
        // Get all gift wrap images
        $giftWrapImages = $this->fileUploadService->getGiftWrapImages($giftWrap['theme_image']);
        
        $data = [
            'user' => $user,
            'giftWrap' => $giftWrap,
            'pageno' => $pageno,
            'currentPageUrl' => '/admin/gift-wraps',
            'theme_name' => $sessionFormData['theme_name'] ?? null,
            'theme_tag' => $sessionFormData['theme_tag'] ?? null,
            'theme_image' => $sessionFormData['theme_image'] ?? null,
            'giftWrapImages' => $giftWrapImages
        ];
        
        return $this->view('admin/gift-wraps/edit', $data);
    }

    public function updateGiftWrap(): Response
    {
        $user = $this->auth();
        
        $id = (int) $this->request->input('theme_id');
        $pageno = (int) $this->request->input('pageno', 1);
        
        if (!$id) {
            return $this->redirect('/admin/gift-wraps')->withError('Invalid gift wrap ID.');
        }
        
        $giftWrap = Theme::find($id);
        if (!$giftWrap || $giftWrap['theme_type'] !== 'Gift Wrap') {
            return $this->redirect('/admin/gift-wraps')->withError('Gift wrap not found.');
        }
        
        $theme_name = trim($this->request->input('theme_name', ''));
        $theme_tag = trim($this->request->input('theme_tag', ''));
        $theme_image = trim($this->request->input('theme_image', ''));
        
        // Validation
        $errors = [];
        if (empty($theme_name)) {
            $errors[] = 'Theme name is required.';
        }
        
        if (empty($theme_tag) || !in_array($theme_tag, ['birthday', 'christmas'])) {
            $errors[] = 'Valid theme tag is required.';
        }
        
        if (empty($theme_image)) {
            $errors[] = 'Theme image is required.';
        }
        
        if (!empty($errors)) {
            // Store form data in session for POST-Redirect-GET pattern
            \App\Services\SessionManager::set('admin_giftwrap_edit_form_data', [
                'theme_name' => $theme_name,
                'theme_tag' => $theme_tag,
                'theme_image' => $theme_image
            ]);
            return $this->redirect("/admin/gift-wraps/edit?id={$id}&pageno={$pageno}")->withError(implode(' ', $errors));
        }
        
        // Update the gift wrap
        $updateData = [
            'theme_name' => $theme_name,
            'theme_tag' => $theme_tag,
            'theme_image' => $theme_image
        ];
        
        if (Theme::update($id, $updateData)) {
            // Clear session form data on success
            \App\Services\SessionManager::remove('admin_giftwrap_edit_form_data');
            return $this->redirect("/admin/gift-wraps?pageno={$pageno}")->withSuccess('Gift wrap updated successfully.');
        }
        
        // Store form data in session for error
        \App\Services\SessionManager::set('admin_giftwrap_edit_form_data', [
            'theme_name' => $theme_name,
            'theme_tag' => $theme_tag,
            'theme_image' => $theme_image
        ]);
        return $this->redirect("/admin/gift-wraps/edit?id={$id}&pageno={$pageno}")->withError('Failed to update gift wrap. Please try again.');
    }

    public function addGiftWrapImage(): void
    {
        $user = $this->auth();
        
        header('Content-Type: application/json');
        
        $themeId = (int) $this->request->input('theme_id');
        $giftWrapFolder = trim($this->request->input('gift_wrap_folder', ''));
        
        if (!$themeId || empty($giftWrapFolder)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid gift wrap ID or folder name.'
            ]);
            exit;
        }
        
        $giftWrap = Theme::find($themeId);
        if (!$giftWrap || $giftWrap['theme_type'] !== 'Gift Wrap') {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Gift wrap not found.'
            ]);
            exit;
        }
        
        if (!$this->request->hasFile('gift_wrap_image')) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'No image file provided.'
            ]);
            exit;
        }
        
        $uploadResult = $this->fileUploadService->uploadGiftWrapImage(
            $this->request->file('gift_wrap_image'),
            $giftWrapFolder
        );
        
        if ($uploadResult['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Image uploaded successfully.',
                'filename' => $uploadResult['filename']
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $uploadResult['error'] ?? 'Failed to upload image.'
            ]);
        }
        exit;
    }

    public function removeGiftWrapImage(): void
    {
        $user = $this->auth();
        
        header('Content-Type: application/json');
        
        $themeId = (int) $this->request->input('theme_id');
        $giftWrapFolder = trim($this->request->input('gift_wrap_folder', ''));
        $filename = trim($this->request->input('filename', ''));
        
        if (!$themeId || empty($giftWrapFolder) || empty($filename)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid parameters.'
            ]);
            exit;
        }
        
        $giftWrap = Theme::find($themeId);
        if (!$giftWrap || $giftWrap['theme_type'] !== 'Gift Wrap') {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Gift wrap not found.'
            ]);
            exit;
        }
        
        $deleteResult = $this->fileUploadService->deleteGiftWrapImage($giftWrapFolder, $filename);
        
        if ($deleteResult) {
            // Reorder remaining images to ensure sequential numbering
            $remainingImages = $this->fileUploadService->getGiftWrapImages($giftWrapFolder);
            if (!empty($remainingImages)) {
                $this->fileUploadService->reorderGiftWrapImages($giftWrapFolder, $remainingImages);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Image removed successfully.'
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to remove image.'
            ]);
        }
        exit;
    }

    public function reorderGiftWrapImages(): void
    {
        $user = $this->auth();
        
        header('Content-Type: application/json');
        
        $themeId = (int) $this->request->input('theme_id');
        $giftWrapFolder = trim($this->request->input('gift_wrap_folder', ''));
        $newOrder = $this->request->input('new_order', []);
        
        if (!$themeId || empty($giftWrapFolder) || !is_array($newOrder) || empty($newOrder)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid parameters.'
            ]);
            exit;
        }
        
        $giftWrap = Theme::find($themeId);
        if (!$giftWrap || $giftWrap['theme_type'] !== 'Gift Wrap') {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Gift wrap not found.'
            ]);
            exit;
        }
        
        $reorderResult = $this->fileUploadService->reorderGiftWrapImages($giftWrapFolder, $newOrder);
        
        if ($reorderResult) {
            echo json_encode([
                'success' => true,
                'message' => 'Images reordered successfully.'
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to reorder images.'
            ]);
        }
        exit;
    }

    public function editUser(): Response
    {
        $user = $this->auth();
        
        $username = trim($this->request->get('username', ''));
        if (empty($username)) {
            return $this->redirect('/admin/users')->withError('Invalid username.');
        }
        
        $editUser = User::whereEqual('username', $username);
        if (!$editUser) {
            return $this->redirect('/admin/users')->withError('User not found.');
        }
        
        $pageno = (int) $this->request->get('pageno', 1);
        
        // Check for form data in session (from validation errors)
        $sessionFormData = \App\Services\SessionManager::get('admin_user_edit_form_data');
        if (\App\Services\SessionManager::has('admin_user_edit_form_data')) {
            \App\Services\SessionManager::remove('admin_user_edit_form_data');
        }
        
        $data = [
            'user' => $user,
            'editUser' => $editUser,
            'pageno' => $pageno,
            'currentPageUrl' => '/admin/users',
            'name' => $sessionFormData['name'] ?? null,
            'email' => $sessionFormData['email'] ?? null,
            'role' => $sessionFormData['role'] ?? null
        ];
        
        return $this->view('admin/users/edit', $data);
    }

    public function updateUser(): Response
    {
        $user = $this->auth();
        
        $username = trim($this->request->input('username', ''));
        $pageno = (int) $this->request->input('pageno', 1);
        
        if (empty($username)) {
            return $this->redirect('/admin/users')->withError('Invalid username.');
        }
        
        $editUser = User::whereEqual('username', $username);
        if (!$editUser) {
            return $this->redirect('/admin/users')->withError('User not found.');
        }
        
        $name = trim($this->request->input('name', ''));
        $email = trim($this->request->input('email', ''));
        $role = trim($this->request->input('role', ''));
        
        // Validation
        $errors = [];
        if (empty($name) || strlen($name) < 2) {
            $errors[] = 'Name must be at least 2 characters.';
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }
        
        if (empty($role) || !in_array($role, ['User', 'Admin'])) {
            $errors[] = 'Valid role is required.';
        }
        
        // Check if email is already in use by another user
        if (!empty($email)) {
            $existingUser = User::findByEmail($email);
            if ($existingUser && $existingUser['username'] !== $username) {
                $errors[] = 'Email is already in use by another user.';
            }
        }
        
        if (!empty($errors)) {
            // Store form data in session for POST-Redirect-GET pattern
            \App\Services\SessionManager::set('admin_user_edit_form_data', [
                'name' => $name,
                'email' => $email,
                'role' => $role
            ]);
            return $this->redirect("/admin/users/edit?username=" . urlencode($username) . "&pageno={$pageno}")->withError(implode(' ', $errors));
        }
        
        // Update the user
        $updateData = [
            'name' => $name,
            'email' => $email ?: null,
            'role' => $role
        ];
        
        if (User::update($editUser['id'], $updateData)) {
            // Clear session form data on success
            \App\Services\SessionManager::remove('admin_user_edit_form_data');
            return $this->redirect("/admin/users?pageno={$pageno}")->withSuccess('User updated successfully.');
        }
        
        // Store form data in session for error
        \App\Services\SessionManager::set('admin_user_edit_form_data', [
            'name' => $name,
            'email' => $email,
            'role' => $role
        ]);
        return $this->redirect("/admin/users/edit?username=" . urlencode($username) . "&pageno={$pageno}")->withError('Failed to update user. Please try again.');
    }

    public function sendPasswordReset(): Response
    {
        $user = $this->auth();
        
        $username = trim($this->request->input('username', ''));
        $pageno = (int) $this->request->input('pageno', 1);
        
        if (empty($username)) {
            return $this->redirect('/admin/users')->withError('Invalid username.');
        }
        
        $editUser = User::whereEqual('username', $username);
        if (!$editUser) {
            return $this->redirect('/admin/users')->withError('User not found.');
        }
        
        if (empty($editUser['email'])) {
            return $this->redirect("/admin/users/edit?username=" . urlencode($username) . "&pageno={$pageno}")->withError('User does not have an email set up.');
        }
        
        // Use the existing password reset functionality
        $emailService = new \App\Services\EmailService();
        $resetToken = bin2hex(random_bytes(32));
        $resetExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Update user with reset token
        User::update($editUser['id'], [
            'reset_token' => $resetToken,
            'reset_expires_at' => $resetExpires
        ]);
        
        // Send reset email
        $resetLink = \App\Core\Config::get('app.url') . "/reset-password?token={$resetToken}";
        $emailService->sendPasswordResetEmail($editUser['email'], $editUser['name'], $resetLink);
        
        return $this->redirect("/admin/users/edit?username=" . urlencode($username) . "&pageno={$pageno}")->withSuccess('Password reset email sent successfully.');
    }

    public function viewWishlist(): Response
    {
        $user = $this->auth();
        
        $id = (int) $this->request->get('id');
        if (!$id) {
            return $this->redirect('/admin/wishlists')->withError('Invalid wishlist ID.');
        }
        
        // Get wishlist without username restriction (admin can view any wishlist)
        $stmt = Database::query("SELECT * FROM wishlists WHERE id = ?", [$id]);
        $wishlist = $stmt->get_result()->fetch_assoc();
        
        if (!$wishlist) {
            return $this->redirect('/admin/wishlists')->withError('Wishlist not found.');
        }
        
        // Get the wishlist owner's username for the service call
        $wishlistUsername = $wishlist['username'];
        
        // Use WishlistService to get items (this will work since we have the username)
        $wishlistService = new WishlistService();
        
        // Get pagination number
        $pageno = (int) $this->request->get('pageno', 1);
        
        // Get other wishlists for copy functionality (from the same user)
        $otherWishlists = $wishlistService->getOtherWishlists($wishlistUsername, $id);
        
        // Add item counts to each wishlist for copy from dropdown
        foreach ($otherWishlists as &$otherWishlist) {
            $otherWishlist['item_count'] = \App\Models\Item::countItems($otherWishlist['id'], $wishlistUsername);
        }
        unset($otherWishlist); // Break reference
        
        // Get sorting/filter preferences from session
        SessionManager::setWishlistContext($id, $pageno);
        
        $sortPreferences = SessionManager::getWisherSortPreferences();
        $sortPriority = $sortPreferences['sort_priority'];
        $sortPrice = $sortPreferences['sort_price'];
        
        $filters = [
            'sort_priority' => $sortPriority,
            'sort_price' => $sortPrice
        ];
        
        // Convert session filters to WishlistService format using FilterService
        $serviceFilters = FilterService::convertWisherSessionFilters($sortPriority, $sortPrice);
        
        // Get ALL items first (for total count and filtering)
        $allItems = $wishlistService->getWishlistItems($id, $serviceFilters);
        
        // Apply pagination to get only 12 items per page
        $paginatedItems = $this->paginationService->paginate($allItems, $pageno);
        $totalPages = $this->paginationService->getTotalPages($allItems);
        $correctedPage = $this->paginationService->getCurrentPage();
        
        // Redirect if page number was out of range
        if ($correctedPage !== $pageno && count($allItems) > 0) {
            return $this->redirect("/admin/wishlists/view?id={$id}&pageno={$correctedPage}");
        }
        
        $data = [
            'user' => $user,
            'wishlist' => $wishlist,
            'items' => $paginatedItems,
            'all_items' => $allItems, // For total count display
            'other_wishlists' => $otherWishlists,
            'pageno' => $pageno,
            'total_pages' => $totalPages,
            'filters' => $filters,
            'wishlist_id' => $id,
            'isAdminView' => true, // Flag to indicate this is an admin view
            'base_url' => "/admin/wishlists/view?id={$id}" // Admin base URL for pagination
        ];
        
        // Reuse the wishlist show view but with admin context
        return $this->view('wishlist/show', $data);
    }

    public function paginateWishlistItems(): void
    {
        $user = $this->auth();
        
        $id = (int) $this->request->get('id');
        if (!$id) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid wishlist ID',
                'html' => '',
                'current' => 1,
                'total' => 1,
                'paginationInfo' => ''
            ]);
            exit;
        }
        
        // Get wishlist without username restriction (admin can view any wishlist)
        $stmt = Database::query("SELECT * FROM wishlists WHERE id = ?", [$id]);
        $wishlist = $stmt->get_result()->fetch_assoc();
        
        if (!$wishlist) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Wishlist not found',
                'html' => '',
                'current' => 1,
                'total' => 1,
                'paginationInfo' => ''
            ]);
            exit;
        }
        
        $page = (int) $this->request->input('new_page', 1);
        
        // Apply session filters for pagination using FilterService
        $sortPreferences = SessionManager::getWisherSortPreferences();
        $sortPriority = $sortPreferences['sort_priority'];
        $sortPrice = $sortPreferences['sort_price'];
        
        $serviceFilters = FilterService::convertWisherSessionFilters($sortPriority, $sortPrice);
        
        // Get items using WishlistService
        $wishlistService = new WishlistService();
        $items = $wishlistService->getWishlistItems($id, $serviceFilters);
        $paginatedItems = $this->paginationService->paginate($items, $page);
        $totalPages = $this->paginationService->getTotalPages($items);
        $totalRows = count($items);
        
        // Generate HTML for items only (no pagination controls)
        $itemsHtml = \App\Services\HtmlGenerationService::generateItemsHtml($paginatedItems, $id, $page);
        
        // Calculate pagination info
        $itemsPerPage = 12;
        $paginationInfoStart = (($page - 1) * $itemsPerPage) + 1;
        $paginationInfoEnd = min($page * $itemsPerPage, $totalRows);
        $paginationInfo = "Showing {$paginationInfoStart}-{$paginationInfoEnd} of {$totalRows} items";
        
        // Clear any output buffering first
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers and output JSON directly
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        $jsonData = [
            'status' => 'success',
            'message' => 'Items loaded successfully',
            'html' => $itemsHtml,
            'current' => $page,
            'total' => $totalPages,
            'paginationInfo' => $paginationInfo
        ];
        
        echo json_encode($jsonData);
        flush();
        exit;
    }
}

