<?php
session_start();

// Chemin du fichier JSON pour la politique de confidentialité
$privacyPolicyFile = './data/privacy_policy.json';

// Charger le contenu actuel de la politique de confidentialité
$privacyPolicy = file_exists($privacyPolicyFile) ? json_decode(file_get_contents($privacyPolicyFile), true) : null;

// Vérifier si le fichier JSON est vide ou inexistant
$title = $privacyPolicy['title'] ?? 'Politique de confidentialité';
$content = $privacyPolicy['content'] ?? 'La politique de confidentialité est en cours de mise à jour. Veuillez revenir plus tard.';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="./css/privacy_policy.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <section class="privacy-policy">
            <h1><?= htmlspecialchars($title) ?></h1>
            <article>
                <p><?= nl2br(htmlspecialchars($content)) ?></p>
            </article>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
