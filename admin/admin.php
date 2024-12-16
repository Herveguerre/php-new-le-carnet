<?php
session_start();

// Vérification du rôle (admin uniquement)
if (!isset($_SESSION['user']) || !is_array($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}



// Vérifier si le blog est activé


// Chargement des fichiers JSON
$usersFile = '../data/users.json';
$messagesFile = '../data/messages.json';
$blogFile = '../data/blog.json';
$produitsFile = '../data/produits.json';
$commentaires = '../data/commentaires.json';

$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
$messages = file_exists($messagesFile) ? json_decode(file_get_contents($messagesFile), true) : [];
$blogPosts = file_exists($blogFile) ? json_decode(file_get_contents($blogFile), true) : [];
$produits = file_exists($produitsFile) ? json_decode(file_get_contents($produitsFile), true) : [];
$commentaires = file_exists($commentaires) ? json_decode(file_get_contents($commentaires), true) : [];
// Comptage des données
$totalUsers = count($users);
$totalMessages = count($messages);
$totalBlogPosts = count($blogPosts);
$totalProduits = isset($produits['produits']) && is_array($produits['produits']) ? count($produits['produits']) : 0;
$totalCommentaires = count($commentaires); //produits commenters
//nombre de commentaires 

// Filtrer les messages non validés du blog
$messagesNonValides = array_filter($blogPosts, fn($post) => isset($post['valide']) && !$post['valide']);
$totalMessagesNonValides = count($messagesNonValides);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Tableau de bord</title>
    
    <link rel="stylesheet" href="../css/admin_admin.css">
</head>
<body>
<?php include 'admin_header.php'; ?>

<main>
    <h1>Tableau de bord</h1>
    <div class="dashboard">
        <div class="card">
            <h2>Utilisateurs</h2>
            <p>Total : <?= $totalUsers ?></p>
            <a href="admin_users.php">Gérer les utilisateurs</a>
        </div>
        <div class="card">
            <h2>Messages</h2>
            <p>Total : <?= $totalMessages ?></p>
            <a href="admin_contact.php">Voir les messages</a>
        </div>
        <?php if ($config['blog_enabled'] ?? false): ?>
            <div class="card">
            <h2>Forum</h2>
            <p>Salons : <?= $totalBlogPosts ?></p>
            <p>Non validés : <?= $totalMessagesNonValides ?></p>
            <a href="admin_blog.php">Gérer le Forum</a>
        </div> 
            <?php endif; ?>
        
        <div class="card">
            <h2>Produits</h2>
            <p>Total : <?= $totalProduits ?></p>
            <a href="admin_produits.php">Gérer les produits</a>
        </div>

        <?php if ($config['commentaires_enabled'] ?? false): ?>
            <div class="card">
            <h2>commentaire </h2>
            
            <p>Nombre produit commenté : <?= $totalCommentaires ?> </p>
            <a href="admin_commentaires.php">Gérer les commentaire </a>
        </div> 
            <?php endif; ?>
    </div>
</main>

</body>
</html>
