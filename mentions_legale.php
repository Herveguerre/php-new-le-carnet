<?php
session_start();
$dataFile = './data/mentions.json';

// Charger les données
$mentionsData = [];
if (file_exists($dataFile)) {
    $mentionsData = json_decode(file_get_contents($dataFile), true);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="site de recttes super facile ">
    <title>Mentions Légales</title>
    <link rel="stylesheet" href="./css/mentions_legale.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
   
        <?php include 'includes/header.php'; ?>
    

    <main style="margin-top: 40px;" >
        <h1>Mentions Légales</h1>

        <?php if (!empty($mentionsData)): ?>
            <?php foreach ($mentionsData as $mention): ?>
                <section>
                    <h2><?= htmlspecialchars($mention['title']) ?></h2>
                    <p><?= nl2br(htmlspecialchars($mention['content'])) ?></p>
                </section>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune mention légale n'est disponible pour le moment.</p>
        <?php endif; ?>
        <p>
        &copy;
        <?= date("Y"); ?>
        HG Développement - Tous droits réservés
      </p>
    </main>

    
        <?php include 'includes/footer.php'; ?>
    
</body>
</html>
