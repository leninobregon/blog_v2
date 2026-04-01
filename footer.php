    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="newsletter-content">
            <div class="newsletter-text">
                <h3><i class="fas fa-envelope"></i> Newsletter</h3>
                <p><?= t('sidebar_newsletter_desc') ?></p>
            </div>
            <form method="post" action="subscribe.php" class="newsletter-form">
                <input type="email" name="email" placeholder="<?= t('sidebar_email_placeholder') ?>" required>
                <input type="text" name="name" placeholder="<?= $currentLang === 'es' ? 'Tu nombre (opcional)' : 'Your name (optional)' ?>">
                <button type="submit"><i class="fas fa-paper-plane"></i> <?= t('sidebar_subscribe') ?></button>
            </form>
        </div>
    </section>
    
    <footer>
        <div style="max-width:1100px;margin:0 auto;">
            <div class="footer-grid">
                <div class="footer-section">
                    <h4><i class="fas fa-blog"></i> <?= CONFIG['site_name'] ?></h4>
                    <p style="color:rgba(255,255,255,0.95);font-weight:600;font-size:1rem;"><?= CONFIG['description'] ?></p>
                </div>
                <div class="footer-section">
                    <h4><i class="fas fa-link"></i> <?= t('footer_links') ?></h4>
                    <a href="index.php"><i class="fas fa-home"></i> <?= t('nav_home') ?></a>
                    <a href="about.php"><i class="fas fa-user"></i> <?= t('nav_about') ?></a>
                    <a href="index.php?cat=Linux"><i class="fab fa-linux"></i> Linux</a>
                    <a href="index.php?cat=Programación"><i class="fas fa-code"></i> Programación</a>
                    <a href="admin/"><i class="fas fa-cog"></i> Admin</a>
                </div>
                <div class="footer-section">
                    <h4><i class="fas fa-share-alt"></i> <?= $currentLang === 'es' ? 'Redes' : 'Social' ?></h4>
                    <?php if(CONFIG['youtube']): ?>
                    <a href="<?= CONFIG['youtube'] ?>" target="_blank"><i class="fab fa-youtube"></i> YouTube</a>
                    <?php endif; ?>
                    <?php if(CONFIG['telegram']): ?>
                    <a href="<?= CONFIG['telegram'] ?>" target="_blank"><i class="fab fa-telegram"></i> Telegram</a>
                    <?php endif; ?>
                    <?php if(CONFIG['facebook']): ?>
                    <a href="<?= CONFIG['facebook'] ?>" target="_blank"><i class="fab fa-facebook"></i> Facebook</a>
                    <?php endif; ?>
                    <?php if(CONFIG['twitter']): ?>
                    <a href="<?= CONFIG['twitter'] ?>" target="_blank"><i class="fab fa-twitter"></i> Twitter</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= CONFIG['author'] ?>. <?= t('footer_rights') ?>.</p>
            </div>
        </div>
    </footer>
</body>
</html>
