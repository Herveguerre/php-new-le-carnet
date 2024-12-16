<?php
session_start();

// Vérification du rôle (admin uniquement)
if (!isset($_SESSION['user']) || !is_array($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$dataFile = '../data/tarifs.json';

// Charger les données
$tarifsData = [];
if (file_exists($dataFile)) {
    $fileContent = file_get_contents($dataFile);
    $tarifsData = json_decode($fileContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Erreur dans le fichier JSON : ' . json_last_error_msg());
    }
    if (!is_array($tarifsData)) {
        $tarifsData = [];
    }
}

// Ajouter un service
if (isset($_POST['add'])) {
    $newService = [
        "id" => time(),
        "service" => $_POST['service'],
        "price" => $_POST['price'],
        "link" => $_POST['link']
    ];
    $tarifsData[] = $newService;
    file_put_contents($dataFile, json_encode($tarifsData, JSON_PRETTY_PRINT));
    $success = "Les informations ont été mises à jour avec succès.";
}

// Supprimer un service
if (isset($_GET['delete'])) {
    $tarifsData = array_filter($tarifsData, function ($item) {
        return $item['id'] != $_GET['delete'];
    });
    file_put_contents($dataFile, json_encode($tarifsData, JSON_PRETTY_PRINT));
    header("Location: admin_tarifs.php");
    exit();
}

// Modifier un service



if (isset($_POST['edit'])) {
    foreach ($tarifsData as &$service) {
        if ($service['id'] == $_POST['id']) {
            $service['service'] = $_POST['service'];
            $service['price'] = $_POST['price'];
            $service['link'] = $_POST['link'];
        }
    }
    file_put_contents($dataFile, json_encode($tarifsData, JSON_PRETTY_PRINT));
    $success = "Les informations ont été mises à jour avec succès.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration des Tarifs</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include 'admin_header.php'; ?>
<main>
    <h1>Gestion des Tarifs</h1>

    <!-- Ajouter un nouveau service -->
    <form method="POST">
        <h2>Ajouter un service</h2>
        <input type="text" name="service" placeholder="Nom du service" required>
        <input type="text" name="price" placeholder="Prix" required>
        <input type="text" name="link" placeholder="Lien descriptif (URL)">
        <button type="submit" name="add">Ajouter</button>
    </form>

    <!-- Tableau des services -->
    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th>Prix</th>
                <th>Lien</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (is_array($tarifsData)): ?>
                <?php foreach ($tarifsData as $tarif): ?>
                    <tr>
                        <form method="POST">
                            <td><input type="text" name="service" value="<?= htmlspecialchars($tarif['service']) ?>" required></td>
                            <td><input type="text" name="price" value="<?= htmlspecialchars($tarif['price']) ?>" required></td>
                            <td><input type="text" name="link" value="<?= htmlspecialchars($tarif['link']) ?>" required></td>
                            <td>
                                <input type="hidden" name="id" value="<?= $tarif['id'] ?>">
                                <button type="submit" name="edit">Modifier</button>
                                <a href="admin_tarifs.php?delete=<?= $tarif['id'] ?>" onclick="return confirm('Supprimer ce service ?')">Supprimer</a>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Aucun tarif disponible.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>
</body>
</html>
