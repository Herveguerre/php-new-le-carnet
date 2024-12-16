<?php
session_start();

// Vérification du rôle (admin uniquement)
if (!isset($_SESSION['user']) || !is_array($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Charger les données des produits et catégories
$dataFile = '../data/produits.json';
if (!file_exists($dataFile)) {
    die('Le fichier produits.json est introuvable.');
}

$dataProduits = json_decode(file_get_contents($dataFile), true);
if (!is_array($dataProduits)) {
    die('Le fichier produits.json est corrompu.');
}

// Traitement des actions pour les produits
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Modification d'un produit
    if ($action === 'modifier_produit') {
        $produitId = (int)$_POST['produit_id'];
    
        // Parcourir les produits pour trouver celui à modifier
        foreach ($dataProduits['produits'] as &$produit) {
            if ($produit['id'] === $produitId) {
                // Mise à jour des champs du produit
                $produit['nom'] = htmlspecialchars($_POST['nom_produit']);
                $produit['description'] = htmlspecialchars($_POST['description_produit']);
                $produit['categorie_id'] = (int)$_POST['categorie_id'];
                $produit['prix'] = (float)$_POST['prix'];
    
                // Récupération et nettoyage des détails
                $produit['details'] = isset($_POST['details']) && is_array($_POST['details']) 
                    ? array_filter(array_map('htmlspecialchars', $_POST['details']), fn($detail) => !empty($detail))
                    : [];
    
                // Gestion de l'image (si une nouvelle image est uploadée)
                if (!empty($_FILES['image']['name'])) {
                    $imagePath = '../upload/' . basename($_FILES['image']['name']);
                    move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
                    $produit['image'] = $imagePath;
                }
                break; // Quitter la boucle une fois le produit trouvé et modifié
            }
        }
    
        // Sauvegarde dans le fichier JSON
        file_put_contents($dataFile, json_encode($dataProduits, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    
    $produit['details'] = array_filter(
        isset($_POST['details']) ? array_map('htmlspecialchars', $_POST['details']) : [],
        fn($detail) => !empty($detail)
    );
    
    


    // Ajout d'un nouveau produit
    if ($action === 'ajouter_produit') {
        $nouveauProduit = [
            'id' => count($dataProduits['produits']) + 1,
            'nom' => htmlspecialchars($_POST['nom_produit']),
            'description' => htmlspecialchars($_POST['description_produit']),
            'details' => isset($_POST['details']) && is_array($_POST['details']) 
            ? array_map('htmlspecialchars', $_POST['details']) 
            : [],
            'categorie_id' => (int)$_POST['categorie_id'],
            'prix' => (float)$_POST['prix'],
            'image' => null,
        ];

        if (!empty($_FILES['image']['name'])) {
            $imagePath = '../upload/' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
            $nouveauProduit['image'] = $imagePath;
        }

        $dataProduits['produits'][] = $nouveauProduit;
    }

    // Enregistrer les modifications
    file_put_contents($dataFile, json_encode($dataProduits, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

}

// Suppression d'un produit
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'supprimer') {
    $id = (int)$_GET['id'];
    $dataProduits['produits'] = array_filter($dataProduits['produits'], fn($produit) => $produit['id'] !== $id);
    file_put_contents($dataFile, json_encode($dataProduits, JSON_PRETTY_PRINT));
    header('Location: admin_produits.php');
    exit();
}

// Filtrer les produits selon les critères
$nomRecherche = $_GET['nom'] ?? '';
$categorieRecherche = (int)($_GET['categorie'] ?? 0);
$produitsFiltres = array_filter($dataProduits['produits'], function ($produit) use ($nomRecherche, $categorieRecherche) {
    $nomCorrespond = empty($nomRecherche) || stripos($produit['nom'], $nomRecherche) !== false;
    $categorieCorrespond = $categorieRecherche === 0 || $produit['categorie_id'] === $categorieRecherche;
    return $nomCorrespond && $categorieCorrespond;
});
$produitsFiltres = array_values($produitsFiltres);

// Charger les données des options
$optionsFile = '../data/data.json';
$options = file_exists($optionsFile) ? json_decode(file_get_contents($optionsFile), true) : [];


// Gestion des options administratives
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_options'])) {
    $options['details_first_enabled'] = isset($_POST['details_first_enabled']);
    $options['details_end_enabled'] = isset($_POST['details_end_enabled']);
    file_put_contents($optionsFile, json_encode($options, JSON_PRETTY_PRINT));
    header('Location: admin_produits.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Produits</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include 'admin_header.php'; ?>
<main>
    <h1>Administration des Produits</h1>
    <a href="admin_categories.php">Gestion des Catégories</a>

    <!-- Formulaire de recherche --> 
    <div class="border">
        <h3>Rechercher un produit</h3>
        <form method="GET" action="admin_produits.php">
            <input type="text" name="nom" placeholder="Rechercher par nom" value="<?= htmlspecialchars($nomRecherche) ?>">
            <select name="categorie">
                <option value="0">Toutes les catégories</option>
                <?php foreach ($dataProduits['categories'] as $categorie): ?>
                    <option value="<?= $categorie['id'] ?>" <?= $categorieRecherche == $categorie['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($categorie['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <!-- Tableau des produits -->
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Détails</th>
            <th>Catégorie</th>
            <th>Image</th>
            <th>Prix</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($produitsFiltres as $produit): ?>
            <tr>
                <form method="POST" enctype="multipart/form-data">
                    <td><?= $produit['id'] ?></td>
                    <td><input type="text" name="nom_produit" value="<?= htmlspecialchars($produit['nom']) ?>" required></td>
                    <td><textarea name="description_produit" required><?= htmlspecialchars($produit['description']) ?></textarea></td>
                    <td>
                    <div class="details-container">
                        <?php foreach ($produit['details'] as $detail): ?>
                            <div>
                                <input type="text" name="details[]" value="<?= htmlspecialchars($detail) ?>">
                                <button type="button" onclick="removeDetailField(this)">Supprimer</button>
                            </div><hr>
                        <?php endforeach; ?>
                        <button type="button" onclick="addDetailField(this)">Ajouter un détail</button>
                    </div>

                    </td>
                    <td>
                        <select name="categorie_id">
                            <?php foreach ($dataProduits['categories'] as $categorie): ?>
                                <option value="<?= $categorie['id'] ?>" <?= $produit['categorie_id'] == $categorie['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categorie['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="file" name="image"></td>
                    <td><input type="number" name="prix" value="<?= $produit['prix'] ?>" step="0.01"></td>
                    <td>
                        <input type="hidden" name="produit_id" value="<?= $produit['id'] ?>">
                        <input type="hidden" name="action" value="modifier_produit">
                        <button type="submit">Modifier</button> <!-- seul le nouveau detail n'est pas sauvegarder -->
                        <a href="?action=supprimer&id=<?= $produit['id'] ?>">Supprimer</a>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

  

    <!-- Formulaire d'ajout d'un nouveau produit -->
    <div class="border">
        <form method="POST" enctype="multipart/form-data">
            <h2>Ajouter un Nouveau Produit</h2>
            <input type="hidden" name="action" value="ajouter_produit">
            <input type="text" name="nom_produit" placeholder="Nom" required>
            <textarea name="description_produit" placeholder="Description" required></textarea>
            <div class="details-container">
                <input type="text" name="details[]" placeholder="Détail">
                <button type="button" onclick="addDetailField(this)">Ajouter un détail</button>
            </div>
            <select name="categorie_id" required>
                <?php foreach ($dataProduits['categories'] as $categorie): ?>
                    <option value="<?= $categorie['id'] ?>"><?= htmlspecialchars($categorie['nom']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="file" name="image">
            <input type="number" name="prix" placeholder="Prix" step="0.01">
            <button type="submit">Ajouter</button>
        </form>
    </div>
</main>
<script>
function removeDetailField(button) {
    const container = button.parentElement;
    container.remove();
}

function addDetailField(button) {
    const container = button.parentElement; // Le conteneur des détails
    const newField = document.createElement('input');
    newField.type = 'text';
    newField.name = 'details[]'; // Obligatoire pour que PHP le traite comme un tableau
    newField.placeholder = 'Nouveau détail';
    container.insertBefore(newField, button); // Ajoute le champ avant le bouton
}
</script>

</body>
</html>
