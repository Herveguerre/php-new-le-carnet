<?php
$dataFile = __DIR__ . '/../data/data.json';
$data = json_decode(file_get_contents($dataFile), true) ?: ['socials' => []];
$socials = $data['socials'] ?? [];
$pagesFile = __DIR__ . '/../data/pages.json';
$pages = file_exists($pagesFile) ? json_decode(file_get_contents($pagesFile), true) : [];

?>

<link rel="stylesheet" href="../css/footer.css">
<footer>
    <div class="footer-container">
        <div class="footer-section">
            <h4>À propos</h4>
            <p><?= htmlspecialchars($data['site_name']) ?></p>
            <p><?= htmlspecialchars($data['address']) ?></p>
            <p><a href="mailto:<?= htmlspecialchars($data['email']) ?>"><?= htmlspecialchars($data['email']) ?></a></p>
            <p><a href="tel:<?= htmlspecialchars($data['phone']) ?>"><?= htmlspecialchars($data['phone']) ?></a></p>
        </div>
        <div class="footer-section">
            <h4>Navigation</h4>
            <ul>
            <?php if ($config['tarifs_enabled'] ?? false): ?>
                <li><a href="tarifs.php">Nos tarifs</a></li> 
            <?php endif; ?>
            <?php foreach ($pages as $page): ?>
                <li>
                    <a href="<?= htmlspecialchars($page['link']) ?>">
                        <?= htmlspecialchars($page['title']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
                <li><a href="services.php">Services</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="mentions_legale.php">Mentions légales</a></li>
                <li><a href="privacy_policy.php">Politique de confidentialité </a></li>
                <li><a href="faq.php">FAQ</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>Suivez-nous</h4>
            <ul class="social-icons">
                <?php foreach ($socials as $social): ?>
                    <li>
                        <a href="<?= htmlspecialchars($social['link']) ?>" target="_blank">
                        <img src="assets/icons/<?= htmlspecialchars($social['logo']) ?>" alt="<?= htmlspecialchars($social['name']) ?>" width="50">
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> - <?= htmlspecialchars($data['site_name']) ?>. Tous droits réservés.</p>
        <p><a href="./mentions_legale.php">Mentions légales</a> | <a href="privacy_policy.php">Politique de confidentialité</a></p>
        <p>Site crée avec 🧡 par HG Dévelopement.</p>
    </div>
</footer>
