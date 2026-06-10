<?php
declare(strict_types=1);
?>
</main>
<footer class="site-footer">
    <div>
        <strong><?= e(APP_NAME) ?></strong>
        <p>Built for trusted local C2C buying, selling, seller growth, and community moderation.</p>
    </div>
    <div class="footer-links">
        <a href="<?= e(url_for('listings.php')) ?>">Browse listings</a>
        <?php if (!is_logged_in() || has_role('buyer')): ?>
            <a href="<?= e(url_for('seller_request.php')) ?>">Become a seller</a>
        <?php endif; ?>
        <a href="<?= e(url_for('about.php')) ?>">Platform goals</a>
    </div>
</footer>
</body>
</html>
