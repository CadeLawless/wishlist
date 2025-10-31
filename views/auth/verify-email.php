<div class="form-container">
    <?php if ($success): ?>
        <h2>Success!</h2>
        <p>Your email has been verified.</p>
        <p><a class='button primary' href='/wishlist/'>Go home</a></p>
    <?php elseif ($expired): ?>
        <p>Uh oh! This email verification link has expired. Try again!</p>
        <p><a href='/wishlist/profile' class='button primary'>Go to your profile</a></p>
    <?php elseif ($notFound): ?>
        <p>Uh oh! No account found for this email verification.</p>
        <p><a href='/wishlist/profile' class='button primary'>Go to your profile</a></p>
    <?php else: ?>
        <p>Uh oh! Something went wrong while trying to verify your email. Please try again or email <a href='mailto:support@cadelawless.com'>support@cadelawless.com</a> for help.</p>
        <p><a href='/wishlist/' class='button primary'>Go home</a></p>
    <?php endif; ?>
</div>

