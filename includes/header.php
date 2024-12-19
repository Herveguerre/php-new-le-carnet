<?php
require_once 'includes/functions.php';
$config = getSiteConfig();
$dataFile = './data/users.json';
$dataFile = './data/data.json';
$style_file = './css/custom_styles.json';
$custom_styles = file_exists($style_file) ? json_decode(file_get_contents($style_file), true) : [];
$pagesFile = './data/pages.json';
$pages = file_exists($pagesFile) ? json_decode(file_get_contents($pagesFile), true) : [];


//gestion meta description
// Récupérer la page actuelle
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Charger les métadonnées depuis meta.json
$metaFile = __DIR__ . '/../data/meta.json';
$metaData = file_exists($metaFile) ? json_decode(file_get_contents($metaFile), true) : [];

// Récupérer la description et le titre de la page actuelle
$meta_description = $metaData[$current_page]['meta_description'] ?? "Description par défaut pour le site web.";
$page_title = $metaData[$current_page]['title'] ?? "Titre par défaut";
?>
<link rel="stylesheet" href="../css/header.css">


<header>
<meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    <link rel="stylesheet" href="../css/style.css">
    <a href="index.php" class="logo">
        <img src="./upload/logo.png" alt="Logo" id="logo"> 
    </a>
    <div class="menu-toggle">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="about.php">À propos</a></li>
            <li><a href="services.php">Services</a></li>
            <?php foreach ($pages as $page): ?>
                <li>
                    <a href="<?= htmlspecialchars($page['link']) ?>">
                        <?= htmlspecialchars($page['title']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <li><a href="contact.php">Contact</a></li>
            <?php if ($config['blog_enabled'] ?? false): ?>
                <li><a href="blog.php">Forum</a></li> 
            <?php endif; ?>
            <?php if ($config['galerie_enabled'] ?? false): ?>
                <li><a href="galerie.php">Galerie</a></li> 
            <?php endif; ?>
            <?php if (isset($_SESSION['user']['username'])): ?>
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <li><a href="admin/admin.php">Administration</a></li>
            <?php endif; ?>          
            <li><a href="logout.php">Déconnexion (<?= htmlspecialchars(string: $_SESSION ['user']['username']) ?>)</a></li>
            <li><a href="account.php">Mon compte</a></li>
            <?php else: ?>
                <li><a href="login.php">Connexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<script>

document.addEventListener("DOMContentLoaded", () => {
    const menuToggle = document.querySelector(".menu-toggle");
    const navMenu = document.querySelector("nav ul");
    const header = document.querySelector("header");

    menuToggle.addEventListener("click", () => {
        menuToggle.classList.toggle("open");
        navMenu.classList.toggle("active");
        header.classList.toggle("menu-open"); // Ajoute ou supprime la classe pour l'animation du logo
    });
});

</script>
