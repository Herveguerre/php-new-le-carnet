<?php
session_start();
$dataFile = './data/users.json';
// Chargement des données JSON
$data = json_decode(file_get_contents('./data/data.json'), true);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo $data['site_name']; ?></title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/services.css">
</head>
<body>
    <?php include './includes/header.php'; ?>

    <main>
    <section class="features">
    <h2><?= htmlspecialchars($data['services']['title']) ?></h2>
    <div class="cards">
        <?php foreach ($data['services']['cards'] as $card): ?>
            <a href="<?= htmlspecialchars($card['link']) ?>" class="card" target="_blank" rel="noopener noreferrer">
                <div>
                    <h3><?= htmlspecialchars($card['title']) ?></h3>
                    <p><?= htmlspecialchars($card['description']) ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
    </main>

    <?php include './includes/footer.php'; ?>
</body>
</html>
