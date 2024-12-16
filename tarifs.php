<?php
session_start();
$dataFile = './data/tarifs.json';


require_once 'includes/functions.php';


// Vérifier si le blog est activé
$config = getSiteConfig();
 if (!($config['tarifs_enabled'] ?? false)) {
    header('Location: /index.php');
    exit;
 }
 
 
 // Charger les données
$tarifsData = [];
if (file_exists($dataFile)) {
    $tarifsData = json_decode(file_get_contents($dataFile), true);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarifs</title>
    <link rel="stylesheet" href="css/tarifs.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include './includes/header.php'; ?>
    <main>
    <h1>Nos Tarifs</h1>
<table>
    <thead>
        <tr><th style="width: 100%;" >
            <b>les tarifs sont données à titre d'information uniquement.</b>
            
        </tr>
        <tr>
            <th>Service</th>
            <th>Prix</th>
            <th>Détails</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tarifsData as $tarif): ?>
            <tr>
                <td><?= htmlspecialchars($tarif['service']) ?></td>
                <td><?= htmlspecialchars($tarif['price']) ?>€</td>
                <td><a href="<?= htmlspecialchars($tarif['link']) ?>" target="_blank" >Voir le descriptif</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    </main>
    <?php include './includes/footer.php'; ?>
    
</body>
</html>
