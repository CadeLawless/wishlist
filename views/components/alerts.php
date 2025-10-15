<?php
// Display success/error messages from session
if (isset($_SESSION['success_message'])) {
    echo '<div class="success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']);
}

// Display flash messages from controller
if (isset($success_message)) {
    echo '<div class="success-message">' . htmlspecialchars($success_message) . '</div>';
}

if (isset($error_message)) {
    echo '<div class="error-message">' . htmlspecialchars($error_message) . '</div>';
}
?>
