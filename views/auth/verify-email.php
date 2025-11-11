<div class="form-container">
    <?php if ($success): ?>
        <h2>Success!</h2>
        <p>Your email has been verified.</p>
        <p><a class='button primary' href='/'>Go home</a></p>
    <?php elseif ($expired): ?>
        <p>Uh oh! This email verification link has expired. Try again!</p>
        <form method="POST" action="/api/resend-verification">
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" />
            <p><button type="submit" class='button primary'>Resend Verification Email</button></p>
        </form>
        <p><a href='/profile' class='button'>Go to your profile</a></p>
    <?php elseif ($notFound): ?>
        <p>Uh oh! No account found for this email verification.</p>
        <p><a href='/profile' class='button primary'>Go to your profile</a></p>
    <?php else: ?>
        <p>Uh oh! Something went wrong while trying to verify your email. Please try again or email <a href='mailto:support@cadelawless.com'>support@cadelawless.com</a> for help.</p>
        <p><a href='/' class='button primary'>Go home</a></p>
    <?php endif; ?>
</div>

