<?php
require_once '../includes/functions.php';

$config = getSiteConfig();
$dataFile = './data/data.json';

// Chargement des données JSON pour afficher le logo
$data = json_decode(file_get_contents('../data/data.json'), true);





// Gestion de l'activation/désactivation du blog
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['blog_enabled'])) {
    $dataFile = '../data/data.json';
    $data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
    
    if (isset($_SESSION['username']) && $_SESSION['username'] === 'Herve') {
        $data['blog_enabled'] = (bool) $_POST['blog_enabled'];
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));

        header('Location: admin_general.php'); // Redirection après modification
        exit();
    } else {
        $error = "Seul l'administrateur Herve peut modifier cette option.";
    }
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admin_header.css">
    <title>Menu Administrateur</title>
</head>

<body>
    <aside class="admin-sidebar">
        <div class="container_aside">
            <h1>Administration</h1>
            <p>Bienvenue,<?= htmlspecialchars(string: $_SESSION ['user']['username']) ?></p>
            
            <nav>
                <ul>
                    <li><a href="../index.php">retour au site</a></li>

                    <li><a href="admin.php">Tableau de bord</a></li>

                    <li><a href="admin_accueil.php">Gestion page d'accueil</a></li>

                    <li><a href="admin_services.php">gestion services page d'accueil</a></li>

                    <li><a href="admin_about.php">Gestion page à propos</a></li>

                    <li><a href="admin_footer.php">Gestion reseaux sociaux</a></li>

                    <li><a href="admin_produits.php">Gestion des produits</a></li>

                    <li><a href="admin_users.php">Gestion des utilisateurs</a></li>

                    <li><a href="admin_general.php">Paramètres généraux</a></li>

                    <li><a href="admin_faq.php">Gestion des FAQ </a></li>

                    <?php if ($config['galerie_enabled'] ?? false): ?>
                        <li><a href="admin_galerie.php">Voir la galerie d'images</a></li>
                    <?php endif; ?>

                    <?php if ($config['blog_enabled'] ?? false): ?>
                        <li><a href="blog.php">Forum</a></li> 
                    <?php endif; ?>

                    <?php if ($config['tarifs_enabled'] ?? false): ?>
                        <li><a href="admin_tarifs.php">Gestion des tarifs </a></li>
                    <?php endif; ?>
                    

                    <li><a style="color: red;" href="../logout.php">Déconnexion</a></li>

                    <li><a href="admin_privacy_policy.php">Gestion de la politique de confidentialité </a></li>
                    <li><a href="admin_mentions_legale.php">Gestion des mentions légales</a></li>
                </ul>
            </nav>
            <img style="width: 100px;" src="../upload/<?= htmlspecialchars($data['logo']) ?>" alt="Logo actuel" class="current-logo">
        </div>
    </aside>
</body>
</html>
