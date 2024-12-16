<?php
session_start();

// Charger les données des produits et catégories
$dataFile = '../data/produits.json';
$dataProduits = json_decode(file_get_contents($dataFile), true);

 // Vérification du rôle (admin uniquement)
 if (!isset($_SESSION['user']) || !is_array($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // Redirection vers la page de connexion
    header('Location: ../login.php');
    exit();
}

// Fonction pour générer un ID unique pour les produits
function genererIdCategorie($categories) {
    $ids = array_column($categories, 'id');
    return count($ids) > 0 ? max($ids) + 1 : 1;
}

// Traitement des actions pour les catégories
// Traitement des actions pour les catégories
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $nomCategorie = htmlspecialchars(trim($_POST['nom_categorie'] ?? ''));
    $categorieId = isset($_POST['categorie_id']) ? intval($_POST['categorie_id']) : null;

    if ($action === 'ajouter_categorie' && !empty($nomCategorie)) {
        // Ajout d'une nouvelle catégorie
        $nouvelleCategorie = [
            'id' => genererIdCategorie($dataProduits['categories']),
            'nom' => $nomCategorie,
        ];
        $dataProduits['categories'][] = $nouvelleCategorie;
    } elseif ($action === 'modifier_categorie' && $categorieId !== null && !empty($nomCategorie)) {
        // Modification d'une catégorie existante
        foreach ($dataProduits['categories'] as &$categorie) {
            if ($categorie['id'] === $categorieId) {
                $categorie['nom'] = $nomCategorie;
                break;
            }
        }
    }

    // Enregistrement des données mises à jour
    file_put_contents($dataFile, json_encode($dataProduits, JSON_PRETTY_PRINT));
    header('Location: admin_categories.php');
    exit;
}

// Suppression d'une catégorie
if (isset($_GET['action'], $_GET['id'], $_GET['type']) && $_GET['action'] === 'supprimer' && $_GET['type'] === 'categorie') {
    $idCategorie = intval($_GET['id']);
    $dataProduits['categories'] = array_filter(
        $dataProduits['categories'],
        fn($categorie) => $categorie['id'] !== $idCategorie
    );

    // Enregistrement des données mises à jour
    file_put_contents($dataFile, json_encode($dataProduits, JSON_PRETTY_PRINT));
    header('Location: admin_produits.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Produits et Catégories</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<style>
    main{
        margin-left: -50px;
    }
</style>

<body>
<?php include 'admin_header.php'; ?>
<main>
    <h1>Administration des Catégories</h1>
    <a href="admin_produits.php">Retour aux Produits</a>
    <!-- Gestion des catégories -->
    
    <section class="gestion_categories">
    <h2>Gestion des Catégories</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Affichage des catégories existantes -->
            <?php foreach ($dataProduits['categories'] as $categorie): ?>
                <tr>
                    <form method="POST">
                        <td><?= $categorie['id'] ?></td>
                        <td>
                            <input type="text" name="nom_categorie" value="<?= htmlspecialchars($categorie['nom']) ?>" required>
                            <input type="hidden" name="action" value="modifier_categorie">
                            <input type="hidden" name="categorie_id" value="<?= $categorie['id'] ?>">
                        </td>
                        <td>
                            <button type="submit">Modifier</button>
                            <a href="?action=supprimer&type=categorie&id=<?= $categorie['id'] ?>">Supprimer</a>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>

            <!-- Formulaire pour ajouter une nouvelle catégorie -->
            <tr>
                <form method="POST">
                    <td>Nouvelle</td>
                    <td>
                        <input type="text" name="nom_categorie" placeholder="Nouvelle catégorie" required>
                        <input type="hidden" name="action" value="ajouter_categorie">
                    </td>
                    <td>
                        <button type="submit">Ajouter</button>
                    </td>
                </form>
            </tr>
        </tbody>
    </table>
    </section>
    </main>
</body>
</html>
   