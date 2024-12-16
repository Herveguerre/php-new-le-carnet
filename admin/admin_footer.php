<?php
session_start();

// Vérification de l'accès administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Chemin du dossier pour les icônes
$targetDir = '../assets/icons/';

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// Charger les données JSON
$dataFile = '../data/data.json';
$data = json_decode(file_get_contents($dataFile), true);

// Assurez-vous que la clé `socials` existe
if (!isset($data['socials']) || !is_array($data['socials'])) {
    $data['socials'] = [];
}

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $socialName = $_POST['social_name'] ?? '';
    $socialLink = $_POST['social_link'] ?? '';
    $uploadedLogo = $_FILES['social_logo'] ?? null;

    if ($socialName && $socialLink && $uploadedLogo) {
        $logoFileName = basename($uploadedLogo['name']);
        $targetFile = $targetDir . $logoFileName;

        // Vérifiez si le réseau social est déjà ajouté
        $exists = false;
        foreach ($data['socials'] as $social) {
            if ($social['name'] === $socialName || $social['link'] === $socialLink) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            // Déplacer le fichier téléchargé
            if (move_uploaded_file($uploadedLogo['tmp_name'], $targetFile)) {
                // Ajouter le réseau social au JSON
                $data['socials'][] = [
                    'name' => htmlspecialchars($socialName),
                    'link' => htmlspecialchars($socialLink),
                    'logo' => $logoFileName
                ];
                file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
            }
        }
    }
}

// Suppression d'un réseau social
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $indexToDelete = $_POST['index'] ?? -1;

    if (isset($data['socials'][$indexToDelete])) {
        // Supprime l'image associée
        $logoPath = $targetDir . $data['socials'][$indexToDelete]['logo'];
        if (file_exists($logoPath)) {
            unlink($logoPath);
        }

        // Supprime l'entrée JSON
        array_splice($data['socials'], $indexToDelete, 1);
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    }
}

// Modification d'un réseau social
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $indexToEdit = $_POST['index'] ?? -1;
    $newName = $_POST['edit_social_name'] ?? '';
    $newLink = $_POST['edit_social_link'] ?? '';
    $uploadedLogo = $_FILES['edit_social_logo'] ?? null;

    if (isset($data['socials'][$indexToEdit]) && $newName && $newLink) {
        // Mettre à jour les valeurs
        $data['socials'][$indexToEdit]['name'] = htmlspecialchars($newName);
        $data['socials'][$indexToEdit]['link'] = htmlspecialchars($newLink);

        if ($uploadedLogo && $uploadedLogo['tmp_name']) {
            $newLogoFileName = basename($uploadedLogo['name']);
            $targetFile = $targetDir . $newLogoFileName;

            // Déplacer le nouveau fichier et supprimer l'ancien
            if (move_uploaded_file($uploadedLogo['tmp_name'], $targetFile)) {
                $oldLogoPath = $targetDir . $data['socials'][$indexToEdit]['logo'];
                if (file_exists($oldLogoPath)) {
                    unlink($oldLogoPath);
                }
                $data['socials'][$indexToEdit]['logo'] = $newLogoFileName;
            }
        }

        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Réseaux Sociaux</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include 'admin_header.php'; ?>
<main>
    <h2>Gestion des réseaux sociaux </h2>
    <h3>pied de page</h3>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div>
            <label for="social_name">Nom du réseau social :</label>
            <input type="text" id="social_name" name="social_name" required>
        </div>
        <div>
            <label for="social_link">Lien :</label>
            <input type="url" id="social_link" name="social_link" required>
        </div>
        <div>
            <label for="social_logo">Logo :</label>
            <input type="file" id="social_logo" name="social_logo" accept="image/*" required>
        </div>
        <div>
            <button type="submit">Ajouter</button>
        </div>
    </form>

    <h3>Réseaux sociaux existants</h3>
    <ul>
        <?php foreach ($data['socials'] as $index => $social): ?>
            <li style="list-style:none; margin-bottom:10px;  padding: 10px; border : 1px solid #000; " >
           
                <img src="../assets/icons/<?= htmlspecialchars($social['logo']) ?>" alt="<?= htmlspecialchars($social['name']) ?>" width="50">
                <strong><?= htmlspecialchars($social['name']) ?></strong> - 
                
                <a href="<?= htmlspecialchars($social['link']) ?>" target="_blank"><?= htmlspecialchars($social['link']) ?></a>
                
                <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="index" value="<?= $index ?>">
                    <button type="submit">Supprimer</button>
                </form>
                <form action="" method="POST" enctype="multipart/form-data" style="display:inline;">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="index" value="<?= $index ?>">
                    <input type="text" name="edit_social_name" value="<?= htmlspecialchars($social['name']) ?>" required>
                    <input type="url" name="edit_social_link" value="<?= htmlspecialchars($social['link']) ?>" required>
                    <input type="file" name="edit_social_logo" accept="image/*">
                    <button type="submit">Modifier</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</main>
</body>
</html>
