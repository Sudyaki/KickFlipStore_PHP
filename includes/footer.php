<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><?= e(SITE_NAME) ?></h3>
            <p>Streetwear, skate &amp; athletic shoes.</p>
        </div>
        <div class="footer-section">
            <h3>Links</h3>
            <ul>
                <li><a href="<?= $root ?? '' ?>index.php">Shop</a></li>
                <li><a href="<?= $root ?? '' ?>cart.php">Cart</a></li>
            </ul>
        </div>
    </div>
    <div class="copyright">&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.</div>
</footer>
</body>
</html>
