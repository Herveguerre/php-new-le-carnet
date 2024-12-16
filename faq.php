<?php
// Charger les données depuis le fichier JSON
$faqFile = 'data/faq.json';
$faqData = file_exists($faqFile) ? json_decode(file_get_contents($faqFile), true) : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ</title>
    <link rel="stylesheet" href="./css/faq.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main style="margin-top: 40px;" >
        <h1>FAQ</h1>
        <?php if (empty($faqData)): ?>
            <p>Aucune FAQ disponible pour le moment.</p>
        <?php else: ?>
            <div class="faq-container">
                <?php foreach ($faqData as $faq): ?>
                    <details class="faq-item">
                        <summary><?= htmlspecialchars($faq['question']); ?></summary>
                        <p><?= htmlspecialchars($faq['answer']); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>
