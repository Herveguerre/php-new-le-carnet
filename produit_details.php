<?php
session_start();
// var_dump($_SESSION); // Affiche les données de la session
// $_SESSION['username'] = $_SESSION['user']['username'];

// Chargement des fichiers nécessaires
require_once 'includes/functions.php';

// Chemins des fichiers JSON
$dataFile = './data/produits.json';

$optionsFile = './data/options.json';
$commentairesFile = './data/commentaires.json';

// Chargement des produits
$produitsData = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
$produits = $produitsData['produits'] ?? [];

// Chargement des options
//$options = file_exists($optionsFile) ? json_decode(file_get_contents($optionsFile), true) : [];
//$commentairesEnabled = $options['commentaires_enabled'] ?? false;
// Chargement des données
$data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
$options = file_exists($optionsFile) ? json_decode(file_get_contents($optionsFile), true) : [];

// Définir commentaires_enabled en fonction de l'emplacement réel
$commentairesEnabled = $options['commentaires_enabled'] ?? $data['commentaires_enabled'] ?? false;

// Chargement des commentaires
$commentairesData = file_exists($commentairesFile) ? json_decode(file_get_contents($commentairesFile), true) : [];

// Vérification de l'ID du produit
$produitId = $_GET['id'] ?? null;
if (!$produitId || !is_numeric($produitId)) {
    die('Produit introuvable.');
}

// Recherche du produit correspondant
$produit = array_filter($produits, fn($p) => $p['id'] == $produitId);
$produit = reset($produit);

if (!$produit) {
    die('Produit introuvable.');
}

// Gestion des commentaires liés au produit
$commentairesProduit = $commentairesData[$produitId] ?? [];

// Ajout d'un nouveau commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contenu']) && !empty(trim($_POST['contenu']))) {
    if (isset($_SESSION['user']['username'])) {
        $nouveauCommentaire = [
            'id' => time(),
            'auteur' => $_SESSION['user']['username'],
            'contenu' => htmlspecialchars(trim($_POST['contenu'])),
            'date' => date('d-m-Y H:i:s'),
            'valide' => true // validé automatiquement 
        ];

        // Ajouter le commentaire pour ce produit
        $commentairesProduit[] = $nouveauCommentaire;
        $commentairesData[$produitId] = $commentairesProduit;

        // Sauvegarder dans le fichier JSON
        file_put_contents($commentairesFile, json_encode($commentairesData, JSON_PRETTY_PRINT));

        // Message de confirmation stocké dans la session
        $_SESSION['message'] = "Merci pour votre commentaire.";

        // Redirection pour éviter un nouvel envoi
        header("Location: produit_details.php?id=$produitId");
        exit; // Toujours terminer le script après une redirection
    } else {
        $message = "Vous devez être connecté pour poster un commentaire.";
    }
}


$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']); // Supprime le message après affichage
//<?= htmlspecialchars($_SESSION['user']['username']) 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="site de recttes super facile ">
    <title><?= htmlspecialchars($produit['nom']) ?></title>
    <link rel="stylesheet" href="./css/produit_details.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main style="margin-top: 40px;" >
        <!-- Section Détails du produit -->
        <section class="produit-detail">
        

            <div class="produit-image">
                <?php if (!empty($produit['image'])): ?>
                    <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
                <?php else: ?>
                    <p>Aucune image disponible pour ce produit.</p>
                <?php endif; ?>
            </div>

            <div class="produit-info">
                <h1><?= htmlspecialchars($produit['nom']) ?></h1>

                <?php if ($options['details_first_enabled'] ?? false): ?>
                    <?php if (!empty($produit['details'])): ?>
                        <h3>Détails :</h3>
                        <ul>
                            <?php foreach ($produit['details'] as $detail): ?>
                                <li><?= htmlspecialchars($detail) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Aucun détail disponible.</p>
                    <?php endif; ?>
                <?php endif; ?>


                <p class="description"><?= htmlspecialchars($produit['description']) ?></p>

                <?php if ($options['details_end_enabled'] ?? false): ?>
                    <?php if (!empty($produit['details'])): ?>
                        <h3>Détails :</h3>
                        <ul>
                            <?php foreach ($produit['details'] as $detail): ?>
                                <li><?= htmlspecialchars($detail) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Aucun détail disponible.</p>
                    <?php endif; ?>
                <?php endif; ?>
                <p>
                    <?php if (!empty($produit['link'])): ?>
                    <a href="<?= htmlspecialchars($produit['link']) ?>" target="_blank" rel="noopener noreferrer">
                        voir la vidéo
                    </a>
                    <?php endif; ?>          
                </p>

                <?php if (!empty($produit['prix'])): ?>
                    <p style="color: brown;" >Prix : <?= number_format($produit['prix'], 2, ',', ' ') ?> €</p>
                <?php endif; ?>
                <a href="produits.php"> Retour aux produits </a>
            </div>
        </section>
        

        <!-- Section Commentaires -->
        <?php if ($config['commentaires_enabled'] ?? false): ?>
            <section class="commentaires">
                <h2>Commentaires</h2>
                <div class="scroll">
                    <!--  <p>Connecté en tant que : copier la ligne 80  </p>-->
                    <?php if (!empty($commentairesProduit)): ?>
                        <?php foreach (array_reverse($commentairesProduit) as $commentaire): ?>
                            <?php if ($commentaire['valide']): ?>
                                <div class="commentaire">
                                    <h3><?= htmlspecialchars($commentaire['auteur']) ?> (<?= htmlspecialchars($commentaire['date']) ?>)</h3>
                                    <p><?= htmlspecialchars($commentaire['contenu']) ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun commentaire pour ce produit.</p>
                    <?php endif; ?>

                </div>
                    <?php if (isset($_SESSION['user']['username'])): ?>
                        <form method="post">
                            <textarea name="contenu" placeholder="Votre commentaire" required></textarea>
                            <input type="submit" value="Poster">
                        </form>
                    <?php else: ?>
                        <p><a href="login.php">Connectez-vous</a> pour poster un commentaire.</p>
                    <?php endif; ?>

                    <?php if (isset($message)): ?>
                        <p><?= $message ?></p>
                    <?php endif; ?>
               
            </section>
        <?php endif; ?>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
