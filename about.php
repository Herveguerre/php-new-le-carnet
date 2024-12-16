<?php
session_start();

$dataFile = './data/about.json';

// Chargement des données
$aboutData = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="site de recttes super facile ">
    <title>À propos</title>
    <link rel="stylesheet" href="./css/about.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include './includes/header.php'; ?>

    <main>
    <section class="about-section">
        <h1>À propos de nous</h1>
        <?php 
        if (isset($aboutData) && is_array($aboutData) && !empty($aboutData)) {
            usort($aboutData, function ($a, $b) {
                return $a['position'] <=> $b['position'];
            });

            foreach ($aboutData as $section): ?>
                <div class="section-item">
                    <h2><?= htmlspecialchars($section['title']) ?></h2>
                    <p><?= nl2br(htmlspecialchars($section['content'])) ?></p>
                    
                    <?php if (!empty($section['image'])): ?>
                        <img src="./assets/about/<?= htmlspecialchars($section['image']) ?>" 
                            alt="<?= htmlspecialchars($section['title']) ?>">
                    <?php endif; ?>

                    <?php if ($section['section'] === 'team' && isset($section['members']) && is_array($section['members'])): ?>
                        <div class="team-section">
                            <h3></h3>
                            <div class="team-members">
                                <?php foreach ($section['members'] as $member): ?>
                                    <div class="team-member">
                                        <img src="./assets/about/<?= htmlspecialchars($member['photo'] ?? 'default-team.png') ?>" 
                                            alt="Photo de <?= htmlspecialchars($member['name'] ?? 'Membre inconnu') ?>">
                                        <h4><?= htmlspecialchars($member['name'] ?? 'Nom non renseigné') ?></h4>
                                        <p><?= htmlspecialchars($member['role'] ?? 'Rôle non défini') ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; 
        } else {
            echo '<p>Aucune information disponible pour la section À propos.</p>';
        }
        ?>
    </section>
    </main>


    <?php include './includes/footer.php'; ?>
</body>
</html>
