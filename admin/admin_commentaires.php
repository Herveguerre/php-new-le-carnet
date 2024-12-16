<?php
session_start();

// Vérification du rôle (admin uniquement)
if (!isset($_SESSION['user']) || !is_array($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
// $config = getSiteConfig();
//  if (!($config['commentaires_enabled'] ?? false)) {
//     header('Location: /admin.php');
//     exit;
//  }
// Chemins des fichiers JSON
$dataFile = '../data/produits.json';

// Chemin du fichier JSON des commentaires
$commentairesFile = '../data/commentaires.json';
$commentairesData = file_exists($commentairesFile) ? json_decode(file_get_contents($commentairesFile), true) : [];

// Suppression d'un commentaire
if (isset($_GET['produit_id'], $_GET['index']) && is_numeric($_GET['index'])) {
    $produitId = $_GET['produit_id'];
    $index = (int)$_GET['index'];

    if (isset($commentairesData[$produitId][$index])) {
        // Supprimer le commentaire
        unset($commentairesData[$produitId][$index]);
        // Ré-indexer les commentaires
        $commentairesData[$produitId] = array_values($commentairesData[$produitId]);

        // Sauvegarder les modifications
        file_put_contents($commentairesFile, json_encode($commentairesData, JSON_PRETTY_PRINT));

        $message = "Commentaire supprimé avec succès.";
    } else {
        $message = "Le commentaire spécifié est introuvable.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des Commentaires</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'admin_header.php'; ?>
<main>
<h1>Gestion des Commentaires</h1>
    <?php if (isset($message)): ?>
        <p><?= $message ?></p>
    <?php endif; ?>
<table>
<?php if (!empty($commentairesData)): ?>
        <?php foreach ($commentairesData as $produitId => $commentaires): ?>
            <thead>
                <tr>
                    <th>
                    <h2>Produit ID : <?= htmlspecialchars(string: $produitId) ?></h2>
                    </th>
                    <th>
                    <h3>nom du produit :                           </h3>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                    <?php if (!empty($commentaires)): ?>
                <ul>
                    <?php foreach ($commentaires as $index => $commentaire): ?>
                        <li>
                            <strong><?= htmlspecialchars(string: $commentaire['auteur']) ?> (<?= htmlspecialchars($commentaire['date']) ?>)</strong> :
                            <?= htmlspecialchars(string: $commentaire['contenu']) ?>
                            <a href="admin_commentaires.php?produit_id=<?= urlencode($produitId) ?>&index=<?= $index ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');">
                                Supprimer
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Aucun commentaire pour ce produit.</p>
            <?php endif; ?>
                    </td>
                    <td></td>
                </tr>
            </tbody>
           
           
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun commentaire disponible.</p>
    <?php endif; ?>
</table>
   
</main>
    
</body>
</html>
