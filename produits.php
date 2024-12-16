<?php
session_start();
$dataFile = './data/users.json';
$pagesFile = './data/pages.json';
$pages = file_exists($pagesFile) ? json_decode(file_get_contents($pagesFile), true) : [];



// Chargement des données JSON
$data = json_decode(file_get_contents('./data/data.json'), true);
?>
<?php
// Charger les données
$dataFile = 'data/produits.json';
$data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

// Initialiser les catégories et produits
$categories = $data['categories'] ?? [];
$produits = $data['produits'] ?? [];

// Trier les produits par date (les plus récents en premier)
//usort($produits, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

// Filtrer les produits par recherche ou catégorie
$search = $_GET['search'] ?? '';
$categorieId = $_GET['categorie'] ?? '';

if (!empty($search)) {
    $produits = array_filter($produits, function ($produit) use ($search) {
        return stripos($produit['nom'], $search) !== false || stripos($produit['description'], $search) !== false;
    });
}

if (!empty($categorieId) && is_numeric($categorieId)) {
    $produits = array_filter($produits, fn($p) => $p['categorie_id'] == $categorieId);
}

// Vérifier si "Tout voir" est activé
$viewAll = isset($_GET['view_all']) && $_GET['view_all'] == '1';

// Trier les produits par catégorie si "Tout voir" est activé
if ($viewAll) {
    $produitsParCategorie = [];
    foreach ($categories as $categorie) {
        $produitsParCategorie[$categorie['id']] = array_filter($produits, fn($p) => $p['categorie_id'] == $categorie['id']);
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="site de recttes super facile ">
    <title>Produits</title>
    <link rel="stylesheet" href="css/produits.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main>
        <h1><?= htmlspecialchars($page['title']) ?></h1>
        
        <!-- Barre de recherche -->
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Rechercher un produit..." value="<?= htmlspecialchars($search) ?>">
            <select name="categorie">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $categorie): ?>
                    <option value="<?= $categorie['id'] ?>" <?= $categorieId == $categorie['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($categorie['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Rechercher</button>
        </form>

        <!-- Bouton "Tout voir" -->
        <form method="GET" class="view-all-form">
            <button type="submit" name="view_all" value="1">Tout voir</button>
        </form>

        <!-- Liste des produits -->
        <?php if ($viewAll): ?>
            <!-- Affichage par onglets -->
            <div class="onglets">
                <?php foreach ($categories as $categorie): ?>
                    <button class="onglet" data-categorie="<?= $categorie['id'] ?>">
                        <?= htmlspecialchars($categorie['nom']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <section class="produits" id="produits-container">
                <!-- Contenu chargé dynamiquement par JS -->
            </section>
        <?php else: ?>
            <!-- Affichage des derniers produits -->
            <section class="produits">
                <?php if (!empty($produits)): ?>
                    <?php foreach ($produits as $produit): ?>
                        <article class="produit">
                            <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
                            <h2><?= htmlspecialchars($produit['nom']) ?></h2>
                            <p><?= htmlspecialchars(substr($produit['description'], 0, 100)) ?>...</p>
                            <a href="produit_details.php?id=<?= $produit['id'] ?>">Voir plus</a>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun produit trouvé.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>
    <?php include 'includes/footer.php'; ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
    const onglets = document.querySelectorAll(".onglet");
    const produitsContainer = document.getElementById("produits-container");

    // Charger les produits d'une catégorie
    const chargerProduits = async (categorieId) => {
        try {
            const response = await fetch("data/produits.json");
            const data = await response.json();

            // Filtrer les produits par catégorie
            const produits = data.produits.filter(
                (produit) => produit.categorie_id === parseInt(categorieId)
            );

            // Afficher les produits
            produitsContainer.innerHTML = produits
                .map(
                    (produit) => `
                <article class="produit">
                    <img src="${produit.image}" alt="${produit.nom}">
                    <h2>${produit.nom}</h2>
                    <p>${produit.description.substring(0, 100)}...</p>
                    <a href="produit_details.php?id=${produit.id}">Voir plus</a>
                </article>
            `
                )
                .join("");
        } catch (error) {
            produitsContainer.innerHTML = "<p>Erreur lors du chargement des produits.</p>";
        }
    };

    // Ajouter des écouteurs sur les onglets
    onglets.forEach((onglet) => {
        onglet.addEventListener("click", () => {
            const categorieId = onglet.dataset.categorie;
            chargerProduits(categorieId);
        });
    });

    // Charger les produits de la première catégorie par défaut
    if (onglets.length > 0) {
        chargerProduits(onglets[0].dataset.categorie);
    }
});

    </script>
</body>
</html>


