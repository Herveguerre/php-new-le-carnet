<?php
session_start();

// Vérification du rôle admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Charger les données
$dataFile = '../data/produits.json';
if (!file_exists($dataFile)) {
    die('Le fichier produits.json est introuvable.');
}
$dataProduits = json_decode(file_get_contents($dataFile), true);
if (!is_array($dataProduits)) {
    die('Données invalides.');
}

$produitModifie = null;

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // Modification d'un produit
    if ($action === 'modifier_produit') {
        $produitId = (int)$_POST['produit_id'];
        foreach ($dataProduits['produits'] as &$produit) {
            if ($produit['id'] === $produitId) {
                $produit['nom'] = htmlspecialchars($_POST['nom']);
                $produit['description'] = htmlspecialchars($_POST['description']);
                $produit['categorie_id'] = (int)$_POST['categorie'];
                $produit['prix'] = (float)$_POST['prix'];
                $produit['details'] = isset($_POST['details']) ? array_filter(array_map('htmlspecialchars', $_POST['details'])) : [];
                $produit['link'] = htmlspecialchars($_POST['link']);
                
                if (!empty($_FILES['image']['name'])) {
                    $imagePath = '../upload/' . basename($_FILES['image']['name']);
                    move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
                    $produit['image'] = $imagePath;
                }
                break;
            }
        }
        file_put_contents($dataFile, json_encode($dataProduits, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // Ajout d'un produit
    if ($action === 'ajouter_produit') {
        $nouveauProduit = [
            'id' => count($dataProduits['produits']) + 1,
            'nom' => htmlspecialchars($_POST['nom']),
            'description' => htmlspecialchars($_POST['description']),
            'categorie_id' => (int)$_POST['categorie'],
            'prix' => (float)$_POST['prix'],
            'details' => isset($_POST['details']) ? array_filter(array_map('htmlspecialchars', $_POST['details'])) : [],
            'link' => htmlspecialchars($_POST['link']),
            'image' => null,
        ];
        if (!empty($_FILES['image']['name'])) {
            $imagePath = '../upload/' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
            $nouveauProduit['image'] = $imagePath;
        }
        $dataProduits['produits'][] = $nouveauProduit;
        file_put_contents($dataFile, json_encode($dataProduits, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

// Remplir le formulaire de modification
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'modifier') {
    $produitId = (int)$_GET['id'];
    foreach ($dataProduits['produits'] as $produit) {
        if ($produit['id'] === $produitId) {
            $produitModifie = $produit;
            break;
        }
    }
}

// Recherche des produits
$nomRecherche = $_GET['nom'] ?? '';
$categorieRecherche = (int)($_GET['categorie'] ?? 0);
$produitsFiltres = array_filter($dataProduits['produits'], function ($produit) use ($nomRecherche, $categorieRecherche) {
    $nomCorrespond = empty($nomRecherche) || stripos($produit['nom'], $nomRecherche) !== false;
    $categorieCorrespond = $categorieRecherche === 0 || $produit['categorie_id'] === $categorieRecherche;
    return $nomCorrespond && $categorieCorrespond;
});

// Charger les données des options
// Charger les options avec vérification

// Initialiser les détails des produits





?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration des Produits</title>
    <link rel="stylesheet" href="../css/admin.css">
    <script>
        function addDetailField() {
            const container = document.querySelector('.details-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'details[]';
            container.appendChild(input);
        }
    </script>
</head>
<body>
<?php include 'admin_header.php'; ?>
<main>
    <h1>Administration des Produits</h1>
    <a class="border" href="admin_categories.php">Gestion des Catégories</a>

    <!-- Formulaire d'ajout/modification -->
    <div class="formulaire">
        <form method="POST" enctype="multipart/form-data">
            <h2><?= $produitModifie ? 'Modifier' : 'Ajouter' ?> un produit</h2>
            <input type="hidden" name="action" value="<?= $produitModifie ? 'modifier_produit' : 'ajouter_produit' ?>">
            <?php if ($produitModifie): ?>
                <input type="hidden" name="produit_id" value="<?= $produitModifie['id'] ?>">
            <?php endif; ?>
            <label>Nom : <input type="text" name="nom" value="<?= $produitModifie['nom'] ?? '' ?>" required></label>
            <label>Description : <textarea name="description" required><?= $produitModifie['description'] ?? '' ?></textarea></label>
            <label>Catégorie :
                <select name="categorie">
                    <?php foreach ($dataProduits['categories'] as $categorie): ?>
                        <option value="<?= $categorie['id'] ?>" <?= isset($produitModifie) && $produitModifie['categorie_id'] == $categorie['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categorie['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Prix : <input type="number" name="prix" value="<?= $produitModifie['prix'] ?? '' ?>" step="0.01"></label>
            <label>Image : <input type="file" name="image"></label>
            <label>Détails : 
                <div class="details-container">
                    <?php if ($produitModifie && $produitModifie['details']): ?>
                        <?php foreach ($produitModifie['details'] as $detail): ?>
                            <input type="text" name="details[]" value="<?= htmlspecialchars($detail) ?>">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <input type="text" name="details[]" placeholder="Ajouter un détail">
                    <?php endif; ?>
                </div>
                <button type="button" onclick="addDetailField()">Ajouter un détail</button>
            </label>
            <label>lien : <input type="text" name="link" value="<?= $produitModifie['link'] ?? '' ?>"></label>
            <button type="submit"><?= $produitModifie ? 'Modifier' : 'Ajouter' ?></button>
        </form>
    </div>

    <!-- Recherche -->
    <form method="GET">
        <input type="text" name="nom" placeholder="Recherche par nom" value="<?= htmlspecialchars($nomRecherche) ?>">
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

    <!-- Tableau des produits -->
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Détails</th>
            <th>Catégorie</th>
            <th>Prix</th>
            <th>lien</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($produitsFiltres as $produit): ?>
            <tr>
                <td><?= $produit['id'] ?></td>
                <td><?= htmlspecialchars($produit['nom']) ?></td>
                <td><?= htmlspecialchars($produit['description']) ?></td>
                <td><?= implode(', ', $produit['details']) ?></td>
                <td><?= htmlspecialchars($dataProduits['categories'][$produit['categorie_id']]['nom'] ?? 'Inconnue') ?></td>                
                <td><?= $produit['prix'] ?> €</td>
                <td><?= htmlspecialchars($produit['link']) ?></td>
                <td>
                    <a href="?action=modifier&id=<?= $produit['id'] ?>">Modifier</a>
                    <a href="?action=supprimer&id=<?= $produit['id'] ?>" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
     
</main>
</body>
</html>
