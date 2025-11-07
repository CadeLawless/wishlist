<?php
// Display flash messages
if (isset($flash['success'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../../public/images/site-images/menu-close.php');
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
                require(__DIR__ . '/../../../public/images/site-images/menu-close.php');
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
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>
    <div class="content">
        <p style="margin: 0 0 20px;"><a class="button accent" href="/admin/gift-wraps<?php echo isset($pageno) ? '?pageno=' . (int)$pageno : ''; ?>">Back to Gift Wraps</a></p>
        
        <div class="form-container">
            <h2>Edit Gift Wrap</h2>
            <form method="POST" action="/admin/gift-wraps/update">
                <input type="hidden" name="theme_id" value="<?php echo htmlspecialchars($giftWrap['theme_id'] ?? ''); ?>">
                <input type="hidden" name="pageno" value="<?php echo isset($pageno) ? (int)$pageno : 1; ?>">
                
                <?php if(isset($error_msg)) echo $error_msg; ?>
                
                <div class="flex form-flex">
                    <div class="large-input">
                        <label for="theme_name">Name:<br></label>
                        <input required type="text" name="theme_name" id="theme_name" value="<?php echo htmlspecialchars($theme_name ?? $giftWrap['theme_name'] ?? ''); ?>" maxlength="100" />
                    </div>
                    
                    <div class="large-input">
                        <label for="theme_tag">Tag:<br></label>
                        <select required name="theme_tag" id="theme_tag">
                            <option value="" disabled>Select an option</option>
                            <option value="birthday" <?php echo (($theme_tag ?? $giftWrap['theme_tag'] ?? '') == 'birthday') ? 'selected' : ''; ?>>Birthday</option>
                            <option value="christmas" <?php echo (($theme_tag ?? $giftWrap['theme_tag'] ?? '') == 'christmas') ? 'selected' : ''; ?>>Christmas</option>
                        </select>
                    </div>
                    
                    <div class="large-input">
                        <label for="theme_image">Image Folder Name:<br></label>
                        <input required type="text" name="theme_image" id="theme_image" value="<?php echo htmlspecialchars($theme_image ?? $giftWrap['theme_image'] ?? ''); ?>" maxlength="255" />
                        <p style="margin-top: 5px; font-size: 0.9em; color: var(--text-secondary);">Folder name in gift-wraps directory (e.g., "wrap1")</p>
                    </div>
                    
                    <div class="large-input center">
                        <input type="submit" class="button text" value="Update Gift Wrap" />
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .form-container {
        margin: 20px auto 30px;
        background-color: var(--background-darker);
        max-width: 500px;
        padding: 20px;
    }
</style>

<script src="/public/js/form-validation.js"></script>
<script>
$(document).ready(function() {
    FormValidator.init('form', {
        theme_name: {
            required: true,
            minLength: 1,
            maxLength: 100
        },
        theme_tag: {
            required: true
        },
        theme_image: {
            required: true,
            minLength: 1,
            maxLength: 255
        }
    });
});
</script>

