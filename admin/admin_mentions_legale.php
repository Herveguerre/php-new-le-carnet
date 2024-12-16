<?php
session_start();

// Vérification du rôle (admin uniquement)
if (!isset($_SESSION['user']) || !is_array($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
   // Redirection vers la page de connexion
   header('Location: ../login.php');
   exit();
}
$dataFile = '../data/mentions.json';

// Charger les données
$mentionsData = [];
if (file_exists($dataFile)) {
    $mentionsData = json_decode(file_get_contents($dataFile), true);
}

// Gérer les actions CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    
    if ($type === 'add') {
        $title = htmlspecialchars($_POST['title']);
        $content = htmlspecialchars($_POST['content']);
        $mentionsData[] = ['title' => $title, 'content' => $content];
    } elseif ($type === 'edit') {
        $index = (int)$_POST['index'];
        $mentionsData[$index]['title'] = htmlspecialchars($_POST['title']);
        $mentionsData[$index]['content'] = htmlspecialchars($_POST['content']);
    } elseif ($type === 'delete') {
        $index = (int)$_POST['index'];
        array_splice($mentionsData, $index, 1);
    }

    // Sauvegarder les données
    file_put_contents($dataFile, json_encode($mentionsData, JSON_PRETTY_PRINT));
    header('Location: admin_mentions_legale.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="site de recttes super facile ">
    <title>Admin - Mentions Légales</title>
    <link rel="stylesheet" href="../css/admin.css">
    
</head>
<style>
    textarea    {
        width: 100%;
        height: 200px;
        resize: none;
    }
</style>
<body><?php include 'admin_header.php'; ?>
    <main>
    <h1>Gestion des Mentions Légales</h1>

<!-- Ajouter une section -->
<section>
    <h2>Ajouter une Section</h2>
    <form method="POST" action="admin_mentions_legale.php">
        <input type="hidden" name="type" value="add">
        <label for="title">Titre :</label>
        <input type="text" id="title" name="title" required>
        <label for="content">Paragraphe :</label>
        <textarea id="content" name="content" required></textarea>
        <button type="submit">Ajouter</button>
    </form>
</section>

<!-- Liste des sections existantes -->
<section>
    <h2>Sections Existantes</h2>
    <?php foreach ($mentionsData as $index => $mention): ?>
        <div class="section">
            <h3><?= htmlspecialchars($mention['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($mention['content'])) ?></p>

            <!-- Modifier une section -->
            <form method="POST" action="admin_mentions_legale.php">
                <input type="hidden" name="type" value="edit">
                <input type="hidden" name="index" value="<?= $index ?>">
                <label for="title-<?= $index ?>">Titre :</label>
                <input type="text" id="title-<?= $index ?>" name="title" value="<?= htmlspecialchars($mention['title']) ?>" required>
                <label for="content-<?= $index ?>">Paragraphe :</label>
                <textarea id="content-<?= $index ?>" name="content" required><?= htmlspecialchars($mention['content']) ?></textarea>
                <button type="submit">Modifier</button>
            </form>

            <!-- Supprimer une section -->
            <form method="POST" action="admin_mentions_legale.php">
                <input type="hidden" name="type" value="delete">
                <input type="hidden" name="index" value="<?= $index ?>">
                <button type="submit" onclick="return confirm('Confirmez-vous la suppression ?')">Supprimer</button>
            </form>
        </div>
    <?php endforeach; ?>
</section>
<
    </main>
    
</body>
</html>
