<?php
session_start();
// Vérification du rôle (admin uniquement)
if (!isset($_SESSION['user']) || !is_array($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
// Charger les données depuis le fichier JSON
$faqFile = '../data/faq.json';
$faqData = file_exists($faqFile) ? json_decode(file_get_contents($faqFile), true) : [];

// Ajouter une FAQ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $newQuestion = htmlspecialchars($_POST['question']);
    $newAnswer = htmlspecialchars($_POST['answer']);
    $faqData[] = ['question' => $newQuestion, 'answer' => $newAnswer];
    file_put_contents($faqFile, json_encode($faqData, JSON_PRETTY_PRINT));
    header("Location: admin_faq.php");
    exit;
}

// Supprimer une FAQ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $index = intval($_POST['index']);
    array_splice($faqData, $index, 1);
    file_put_contents($faqFile, json_encode($faqData, JSON_PRETTY_PRINT));
    header("Location: admin_faq.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin FAQ</title>
    <link rel="stylesheet" href="../css/admin_faq.css">
    <link rel="stylesheet" href="../css/admin.css">
    
</head>
<body>
    <?php include 'admin_header.php'; ?>
    <main>
        <h1>Gestion des FAQ</h1>

        <h2>Ajouter une FAQ</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <label for="question">Question :</label>
            <textarea name="question" id="question" required></textarea><br>
            <label for="answer">Réponse :</label>
            <textarea name="answer" id="answer" required></textarea><br>
            <button class="btn" type="submit">Ajouter</button>
        </form>

        <h2>Liste des FAQ</h2>
        <?php if (empty($faqData)): ?>
            <p>Aucune FAQ disponible.</p>
        <?php else: ?>
            <ul class="faq-list" >
                <?php foreach ($faqData as $index => $faq): ?>
                    <li>
                        <strong><?= htmlspecialchars($faq['question']); ?></strong><br>
                        <?= htmlspecialchars($faq['answer']); ?>
                        <form class="delete-form" method="POST" action="" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="index" value="<?= $index; ?>">
                            <button  type="submit">Supprimer</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>
