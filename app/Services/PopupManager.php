<?php

namespace App\Services;

class PopupManager
{
    /**
     * Generate a popup container with close button
     */
    public static function generatePopupContainer(array $options = []): string
    {
        $id = $options['id'] ?? '';
        $classes = $options['classes'] ?? '';
        $type = $options['type'] ?? 'standard'; // standard, first, second, image, fullscreen
        $closeButton = $options['closeButton'] ?? true;
        
        // Build class string
        $containerClasses = 'popup-container';
        if ($type !== 'standard') {
            $containerClasses .= " {$type}";
        }
        if ($classes) {
            $containerClasses .= " {$classes}";
        }
        
        $containerId = $id ? " id=\"{$id}\"" : '';
        
        ob_start();
        ?>
        <div class="<?php echo $containerClasses; ?>"<?php echo $containerId; ?>>
            <div class="popup<?php echo $type === 'fullscreen' ? ' fullscreen' : ''; ?>">
                <?php if ($closeButton): ?>
                <div class="close-container">
                    <a href="#" class="close-button">
                        <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                    </a>
                </div>
                <?php endif; ?>
                <div class="popup-content">
                    <?php echo $options['content'] ?? ''; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Generate a confirmation popup
     */
    public static function generateConfirmationPopup(array $options): string
    {
        $title = $options['title'] ?? 'Confirm Action';
        $message = $options['message'] ?? 'Are you sure you want to proceed?';
        $yesText = $options['yesText'] ?? 'Yes';
        $noText = $options['noText'] ?? 'No';
        $yesUrl = $options['yesUrl'] ?? '#';
        $noUrl = $options['noUrl'] ?? '#';
        $yesClass = $options['yesClass'] ?? 'button primary';
        $noClass = $options['noClass'] ?? 'button secondary no-button';
        $type = $options['type'] ?? 'first';
        $classes = $options['classes'] ?? '';
        
        $content = "
            <h2 style='margin-top: 0;'>{$title}</h2>
            <p>{$message}</p>
            <div style='margin: 16px 0;' class='center'>
                <a class='{$noClass}' href='{$noUrl}'>{$noText}</a>
                <a class='{$yesClass}' href='{$yesUrl}'>{$yesText}</a>
            </div>
        ";
        
        return self::generatePopupContainer([
            'type' => $type,
            'classes' => $classes,
            'content' => $content
        ]);
    }
    
    /**
     * Generate a form popup
     */
    public static function generateFormPopup(array $options): string
    {
        $title = $options['title'] ?? 'Form';
        $formAction = $options['formAction'] ?? '#';
        $formMethod = $options['formMethod'] ?? 'POST';
        $formContent = $options['formContent'] ?? '';
        $submitText = $options['submitText'] ?? 'Submit';
        $cancelText = $options['cancelText'] ?? 'Cancel';
        $type = $options['type'] ?? 'first';
        $classes = $options['classes'] ?? '';
        
        $content = "
            <h2 style='margin-top: 0;'>{$title}</h2>
            <form method='{$formMethod}' action='{$formAction}'>
                {$formContent}
                <div style='margin: 16px 0;' class='center'>
                    <a class='button secondary no-button' href='#'>{$cancelText}</a>
                    <button type='submit' class='button primary'>{$submitText}</button>
                </div>
            </form>
        ";
        
        return self::generatePopupContainer([
            'type' => $type,
            'classes' => $classes,
            'content' => $content
        ]);
    }
    
    /**
     * Generate an info popup (for success messages, etc.)
     */
    public static function generateInfoPopup(array $options): string
    {
        $title = $options['title'] ?? 'Information';
        $message = $options['message'] ?? '';
        $type = $options['type'] ?? 'standard';
        $classes = $options['classes'] ?? '';
        $showCloseButton = $options['showCloseButton'] ?? true;
        
        $content = "
            <h2 style='margin-top: 0;'>{$title}</h2>
            <p>{$message}</p>
        ";
        
        return self::generatePopupContainer([
            'type' => $type,
            'classes' => $classes,
            'content' => $content,
            'closeButton' => $showCloseButton
        ]);
    }
    
    /**
     * Generate a popup button
     */
    public static function generatePopupButton(array $options): string
    {
        $text = $options['text'] ?? 'Button';
        $icon = $options['icon'] ?? '';
        $classes = $options['classes'] ?? 'button primary';
        $href = $options['href'] ?? '#';
        $id = $options['id'] ?? '';
        
        $buttonId = $id ? " id=\"{$id}\"" : '';
        
        ob_start();
        ?>
        <a class="<?php echo $classes; ?> popup-button" href="<?php echo $href; ?>"<?php echo $buttonId; ?>>
            <?php if ($icon): ?>
                <?php require(__DIR__ . "/../../public/images/site-images/icons/{$icon}.php"); ?>
            <?php endif; ?>
            <span><?php echo htmlspecialchars($text); ?></span>
        </a>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Generate an icon popup button
     */
    public static function generateIconPopupButton(array $options): string
    {
        $text = $options['text'] ?? 'Button';
        $icon = $options['icon'] ?? '';
        $classes = $options['classes'] ?? 'icon-container';
        $href = $options['href'] ?? '#';
        $id = $options['id'] ?? '';
        
        $buttonId = $id ? " id=\"{$id}\"" : '';
        
        ob_start();
        ?>
        <a class="<?php echo $classes; ?> popup-button" href="<?php echo $href; ?>"<?php echo $buttonId; ?>>
            <?php if ($icon): ?>
                <?php require(__DIR__ . "/../../public/images/site-images/icons/{$icon}.php"); ?>
            <?php endif; ?>
            <div class="inline-label"><?php echo htmlspecialchars($text); ?></div>
        </a>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Show a popup by ID
     */
    public static function showPopup(string $popupId): string
    {
        return "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const popup = document.getElementById('{$popupId}');
                    if (popup) {
                        popup.classList.remove('hidden');
                        popup.querySelector('.popup').classList.add('active');
                        document.body.classList.add('fixed');
                    }
                });
            </script>
        ";
    }
    
    /**
     * Hide a popup by ID
     */
    public static function hidePopup(string $popupId): string
    {
        return "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const popup = document.getElementById('{$popupId}');
                    if (popup) {
                        popup.classList.add('hidden');
                        popup.querySelector('.popup').classList.remove('active');
                        document.body.classList.remove('fixed');
                    }
                });
            </script>
        ";
    }
}
