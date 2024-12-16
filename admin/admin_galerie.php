<?php
session_start();


 // Vérification du rôle (admin uniquement)
 if (!isset($_SESSION['user']) || !is_array($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // Redirection vers la page de connexion
    header('Location: ../login.php');
    exit();
}

$dataProduitsFile = '../data/produits.json';
$galerieFile = '../upload/galerie.json';

// Charger les données des produits
$dataProduitsFile = '../data/produits.json';

// Charger les données des produits
$dataProduits = file_exists($dataProduitsFile) ? json_decode(file_get_contents($dataProduitsFile), true) : ['produits' => []];
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Galerie</title>
    
    <link rel="stylesheet" href="../css/a dmin.css">
    <style>
        main {
            padding: 20px;
            
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        img {
            max-width: 100px;
            max-height: 100px;
        }
        .message {
            color: green;
            margin-bottom: 15px;
        }
        .scroll {
            height: 900px;
            overflow: hidden;
            overflow-y: scroll;
        }
        .fixed {
            position: sticky;
            top: -1px;
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <main>
        <h1>vue de la Galerie des Produits</h1>
        <div class="scroll">
        <table>
            <thead class="fixed" >
                <tr>
                    <th>ID</th>
                    <th>Nom du Produit</th>
                    <th>Image</th>
                </tr>
            </thead>
            
            <tbody >
                <?php foreach ($dataProduits['produits'] as $produit): ?>
                    <tr>
                        <td><?= htmlspecialchars($produit['id']) ?></td>
                        <td><?= htmlspecialchars($produit['nom']) ?></td>
                        <td><img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>" /></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

            
            
        </table></div>
    </main>

    
</body>
</html>
