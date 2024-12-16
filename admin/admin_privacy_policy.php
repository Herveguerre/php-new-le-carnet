<?php
session_start();

// Vérification du rôle (admin uniquement)
if (!isset($_SESSION['user']) || !is_array($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
// Chemin du fichier JSON pour la politique de confidentialité
$privacyPolicyFile = '../data/privacy_policy.json';

// Charger le contenu actuel de la politique de confidentialité
$privacyPolicy = file_exists($privacyPolicyFile) ? json_decode(file_get_contents($privacyPolicyFile), true) : [];

// Sauvegarder les modifications
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $privacyPolicy['title'] = $_POST['title'] ?? $privacyPolicy['title'];
    $privacyPolicy['content'] = $_POST['content'] ?? $privacyPolicy['content'];

    // Écrire les données dans le fichier JSON
    file_put_contents($privacyPolicyFile, json_encode($privacyPolicy, JSON_PRETTY_PRINT));

    // Message de confirmation
    $_SESSION['message'] = "La politique de confidentialité a été mise à jour.";
    header('Location: admin_privacy_policy.php');
    exit;
}

$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer la politique de confidentialité</title>
    <link rel="stylesheet" href="../css/admin_privacy_policy.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <main>
        <section class="admin-policy">
            <h1>Gérer la politique de confidentialité</h1>

            <?php if ($message): ?>
                <div class="message">
                    <p><?= htmlspecialchars($message) ?></p>
                </div>
            <?php endif; ?>

            <form method="post">
                <label for="title">Titre :</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($privacyPolicy['title'] ?? 'Politique de confidentialité') ?>" required>

                <label for="content">Contenu :</label>
                <textarea id="content" name="content" rows="10" required><?= htmlspecialchars($privacyPolicy['content'] ?? '') ?></textarea>

                <button type="submit">Enregistrer</button>
            </form>
        </section>
    </main>

  
</body>
</html>
