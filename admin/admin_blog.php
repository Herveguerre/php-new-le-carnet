<?php
ob_start();
session_start();
require_once '../includes/functions.php';

// Vérification du rôle (admin uniquement)
if (!isset($_SESSION['user']) || !is_array($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Chemin du fichier JSON
$blogFile = '../data/blog.json';
$settingsFile = '../data/settings.json';

// Charger les données des salons
$blogData = file_exists($blogFile) ? json_decode(file_get_contents($blogFile), true) : [];

// Inverser l'ordre des messages pour chaque salon
foreach ($blogData as $salon => &$messages) {
    $messages = array_reverse($messages);
}
unset($messages); // Bonne pratique pour éviter des effets de bord

// Charger les paramètres (validation automatique)
$settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : ['auto_validate' => false];

// Fonction pour valider un message
function validerMessage(&$blogData, $salon, $index) {
    if (isset($blogData[$salon][$index])) {
        $blogData[$salon][$index]['validated'] = true;
    }
}

// Fonction pour supprimer un message
function supprimerMessage(&$blogData, $salon, $index) {
    if (isset($blogData[$salon][$index])) {
        array_splice($blogData[$salon], $index, 1);
    }
}

// Gestion des actions (Valider ou Supprimer)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salon'], $_POST['index'], $_POST['action'])) {
    $salon = $_POST['salon'];
    $index = (int) $_POST['index'];

    if ($_POST['action'] === 'validate') {
        validerMessage($blogData, $salon, $index);
        $success = "Le message a été validé avec succès.";
    } elseif ($_POST['action'] === 'delete') {
        supprimerMessage($blogData, $salon, $index);
        $success = "Le message a été supprimé.";
    }

    // Enregistrez les modifications dans le fichier JSON
    file_put_contents($blogFile, json_encode($blogData, JSON_PRETTY_PRINT));
}

// Gestion de la modification des noms des salons
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ancien_nom'], $_POST['nouveau_nom'])) {
    $ancienNom = trim($_POST['ancien_nom']);
    $nouveauNom = trim($_POST['nouveau_nom']);

    if (!empty($ancienNom) && !empty($nouveauNom) && isset($blogData[$ancienNom])) {
        if (!isset($blogData[$nouveauNom])) {
            $blogData[$nouveauNom] = $blogData[$ancienNom];
            unset($blogData[$ancienNom]);

            file_put_contents($blogFile, json_encode($blogData, JSON_PRETTY_PRINT));
            $success = "Le salon '$ancienNom' a été renommé en '$nouveauNom'.";
        } else {
            $error = "Le salon '$nouveauNom' existe déjà. Veuillez choisir un autre nom.";
        }
    } else {
        $error = "Erreur lors de la modification du nom du salon.";
    }
}

// Gestion de la validation automatique
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation automatique
    if (isset($_POST['toggle_auto_validate'])) {
        $settings['auto_validate'] = !$settings['auto_validate'];
        file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));

        $success = $settings['auto_validate'] ? "Validation automatique activée." : "Validation automatique désactivée.";
    }

    // Valider ou supprimer un message
    if (isset($_POST['salon'], $_POST['index'], $_POST['action'])) {
        $salon = $_POST['salon'];
        $index = (int) $_POST['index'];

        if ($_POST['action'] === 'validate') {
            validerMessage($blogData, $salon, $index);
            $success = "Le message a été validé avec succès.";
        } elseif ($_POST['action'] === 'delete') {
            supprimerMessage($blogData, $salon, $index);
            $success = "Le message a été supprimé.";
        }

        file_put_contents($blogFile, json_encode($blogData, JSON_PRETTY_PRINT));
    }
}

// Validation automatique en action
if ($settings['auto_validate']) {
    foreach ($blogData as $salon => &$messages) {
        foreach ($messages as &$msg) {
            if (empty($msg['validated'])) {
                $msg['validated'] = true;
            }
        }
    }
    unset($messages);
    file_put_contents($blogFile, json_encode($blogData, JSON_PRETTY_PRINT));
}

ob_end_flush();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - forum</title>
    <link rel="stylesheet" href="../css/admin_blog.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    <main>
        <h1>Gestion du Forum</h1>

        <!-- Bouton pour activer/désactiver la validation automatique -->
        <form method="POST" class="auto-validate-toggle">
    <button type="submit" name="toggle_auto_validate">
        <?= $settings['auto_validate'] ? 'Désactiver' : 'Activer' ?> la validation automatique
    </button>
</form>


        <div class="tabs">
            <?php foreach ($blogData as $salon => $messages): ?>
                <?php $messages = array_reverse($messages); // Inverser l'ordre des messages ?>
                <div class="tab">
                    <h2>
                        <?= htmlspecialchars($salon) ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="ancien_nom" value="<?= htmlspecialchars($salon) ?>">
                            <input type="text" name="nouveau_nom" placeholder="Renommer le salon" required>
                            <button type="submit">Modifier</button>
                        </form>
                    </h2>
                    <ul>
                        <?php foreach ($messages as $index => $msg): ?>
                            <?php if (!is_array($msg) || !isset($msg['username'], $msg['message'], $msg['timestamp'])) continue; ?>
                            <li>
                                <strong><?= htmlspecialchars($msg['username']) ?> :</strong>
                                <?= htmlspecialchars($msg['message']) ?>
                                
                                <form method="POST" class="admin-actions">
                                    <input type="hidden" name="salon" value="<?= htmlspecialchars($salon) ?>">
                                    <input type="hidden" name="index" value="<?= $index ?>">

                                    <!-- Bouton Valider : Affiché uniquement si le message n'est pas validé -->
                                    <?php if (empty($msg['validated'])): ?>
                                        <button class="validate-button" type="submit" name="action" value="validate">Valider</button>
                                    <?php endif; ?>

                                    <button type="submit" name="action" value="delete">Supprimer</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
