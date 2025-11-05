<div class="form-container">
    <p><?php echo htmlspecialchars($error ?? 'Invalid reset link.'); ?></p>
    <p><a href="<?php echo htmlspecialchars($link_url ?? '/login'); ?>" class='button primary'><?php echo htmlspecialchars($link_text ?? 'Go to Login'); ?></a></p>
</div>

