<?php
session_start();

// Vérification du rôle (admin uniquement)
if (!isset($_SESSION['user']) || !is_array($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$welcomeMessage = isset($_SESSION['user']) 
    ? "<p>Bienvenue, " . htmlspecialchars($_SESSION['user']['username']) . ".</p><p>Rôle : " . htmlspecialchars($_SESSION['user']['role']) . ".</p>" 
    : "<p>Vous n'êtes pas connecté.</p>";

// Chemins des fichiers
$dataFile = '../data/data.json';
$optionsFile = '../data/options.json';

// Charger les données
$data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
$options = file_exists($optionsFile) ? json_decode(file_get_contents($optionsFile), true) : [];

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Identifier quel formulaire est soumis
    if (isset($_POST['form_type'])) {
        switch ($_POST['form_type']) {
            case 'general_settings':
                // Mise à jour des paramètres généraux
                $data['site_name'] = htmlspecialchars($_POST['site_name']);
                $data['email'] = htmlspecialchars($_POST['email']);
                $data['address'] = htmlspecialchars($_POST['address']);
                $data['phone'] = htmlspecialchars($_POST['phone']);

                $data['description_site'] = htmlspecialchars($_POST['description_site']);

                // Gestion du logo
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                    $uploadDir = '../upload/';
                    $fileExtension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

                    if (in_array($fileExtension, $allowedExtensions)) {
                        $newFileName = 'logo.' . $fileExtension;
                        $uploadPath = $uploadDir . $newFileName;

                        if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
                            $data['logo'] = $newFileName;
                        } else {
                            $error = "Erreur lors du téléchargement du logo.";
                        }
                    } else {
                        $error = "Extension de fichier non autorisée.";
                    }
                }

                // Enregistrer les modifications dans data.json
                if (!isset($error)) {
                    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
                    header('Location: admin_general.php');
                    exit();
                }
                break;

            case 'developer_options':
                // Mise à jour des options développeur (uniquement pour Herve)
                if ($_SESSION['user']['username'] === 'herve') {
                    $data['blog_enabled'] = isset($_POST['blog_enabled']);
                    $data['galerie_enabled'] = isset($_POST['galerie_enabled']);
                    $data['commentaires_enabled'] = isset($_POST['commentaires_enabled']);
                    $data['tarifs_enabled'] = isset($_POST['tarifs_enabled']);

                    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
                    header('Location: admin_general.php');
                    exit();
                } else {
                    $error = "Vous n'êtes pas autorisé à modifier ces options.";
                }
                break;

            case 'client_options':
                // Mise à jour des options client
                $options['details_first_enabled'] = isset($_POST['details_first_enabled']);
                $options['details_end_enabled'] = isset($_POST['details_end_enabled']);

                file_put_contents($optionsFile, json_encode($options, JSON_PRETTY_PRINT));
                header('Location: admin_general.php');
                exit();
                
        }
    }
}

//changer le nom de la page produit
// Charger les données
$pagesFile = '../data/pages.json';
$pages = file_exists($pagesFile) ? json_decode(file_get_contents($pagesFile), true) : [];

// Gestion de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedTitle = $_POST['produits_title'] ?? null;
    if ($updatedTitle) {
        $pages['produits']['title'] = htmlspecialchars($updatedTitle);
        file_put_contents($pagesFile, json_encode($pages, JSON_PRETTY_PRINT));
        $successMessage = "Le titre de la page Produits a été mis à jour.";
    }
}

//meta description
/// Charger les métadonnées depuis meta.json
$metaFile = '../data/meta.json';
$metaData = file_exists($metaFile) ? json_decode(file_get_contents($metaFile), true) : [];

// Gestion de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($metaData as $key => $data) {
        if (isset($_POST[$key . '_meta_description'])) {
            $metaData[$key]['meta_description'] = htmlspecialchars($_POST[$key . '_meta_description']);
        }
    }
    // Sauvegarder les modifications dans meta.json
    if (file_put_contents($metaFile, json_encode($metaData, JSON_PRETTY_PRINT))) {
        $successMessage = "Les descriptions des balises <meta> ont été mises à jour.";
    } else {
        $errorMessage = "Une erreur est survenue lors de l'enregistrement.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Informations générales</title>
    <link rel="stylesheet" href="../css/admin_general.css">
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .logo{
            width: clamp(5rem, 5vw, 10rem);
        }
        .button {
            padding: 10px 20px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            background: #007bff;
            color: #fff;
            border-radius: 5px;
        }
        .button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
<?php include 'admin_header.php'; ?>

<main>
    <?= $welcomeMessage ?>

    <?php if (isset($success)): ?>
        <p class="success"><?= $success ?></p>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <a class="button" href="admin_style.php">Gestion des styles</a>

    <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="form_type" value="general_settings">

    <label for="site_name">Nom du site :</label>
    <input type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($data['site_name'] ?? '') ?>" required>

    <label for="email">Adresse email :</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" required>

    <label for="address">Adresse postale :</label>
    <textarea id="address" name="address" rows="3" required><?= htmlspecialchars($data['address'] ?? '') ?></textarea>

    <label for="phone">Téléphone :</label>
    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>" required>

    <label for="logo">Logo du site :</label>
    <?php if (!empty($data['logo'])): ?>
        <img class="logo" src="../upload/<?= htmlspecialchars($data['logo']) ?>" alt="Logo actuel">
    <?php endif; ?>
    <input type="file" id="logo" name="logo" accept="image/*">

    <label for="description">Description du site :</label>
    <textarea id="description" name="description" ></textarea>
    

    <button type="submit">Enregistrer les modifications</button>
</form>

<form method="POST">
    <input type="hidden" name="form_type" value="client_options">

    <h3>Options client</h3>
    <label class="border" >
        <input type="checkbox" name="details_first_enabled" <?= !empty($options['details_first_enabled']) ? 'checked' : '' ?>> Afficher les détails en premier
    </label>
    <label class="border" >
        <input type="checkbox" name="details_end_enabled" <?= !empty($options['details_end_enabled']) ? 'checked' : '' ?>> Afficher les détails en dernier
    </label>
    <button type="submit">Mettre à jour les options client</button>
</form>

<form method="POST">
    <input type="hidden" name="form_type" value="developer_options">

    <h3>Options développeur</h3>
    <label class="border" >
        <input type="checkbox" name="blog_enabled" <?= ($data['blog_enabled'] ?? false) ? 'checked' : ''; ?>> Activer le Forum
    </label>
    <label class="border" >
        <input type="checkbox" name="tarifs_enabled" <?= ($data['tarifs_enabled'] ?? false) ? 'checked' : ''; ?>> Activer la page tarifs
    </label>
    <label class="border" >
        <input type="checkbox" name="galerie_enabled" <?= ($data['galerie_enabled'] ?? false) ? 'checked' : ''; ?>> Activer la galerie
    </label>
    <label class="border" >
        <input type="checkbox" name="commentaires_enabled" <?= ($data['commentaires_enabled'] ?? false) ? 'checked' : ''; ?>> Activer les commentaires
    </label>
    <button type="submit">Enregistrer les options développeur</button>
</form>

<section>
<?php if (!empty($successMessage)): ?>
            <p class="success"><?= $successMessage; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="produits_title">Nom affiché pour la page "Produits" :</label>
            <input 
                type="text" 
                id="produits_title" 
                name="produits_title" 
                value="<?= htmlspecialchars($pages['produits']['title'] ?? 'Produits') ?>" 
                required
            >
            <button type="submit">Enregistrer</button>
        </form>
</section>

<section>
    <h2>meta description de chaque page</h2>
    <p style="justify-content: content;" >La meta description est une balise importante, car elle est utilisée pour le référencement du site contenant la page. On recommande d'utiliser cette balise pour améliorer la pertinence des recherches sur la Toile.</p>
    <?php if (!empty($successMessage)): ?>
            <p class="success"><?= $successMessage; ?></p>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <p class="error"><?= $errorMessage; ?></p>
        <?php endif; ?>

        <form method="POST">
            <?php if (!empty($metaData)): ?>
                <?php foreach ($metaData as $key => $data): ?>
                    <fieldset>
                        <legend>Page : <?= htmlspecialchars($data['title'] ?? $key) ?></legend>
                        <label for="<?= $key ?>_meta_description">Description <meta> :</label>
                        <textarea 
                        style="width: 80%; resize: vertical; "
                            id="<?= $key ?>_meta_description" 
                            name="<?= $key ?>_meta_description" 
                            rows="3" 
                            required><?= htmlspecialchars($data['meta_description'] ?? '') ?></textarea>
                    </fieldset>
                <?php endforeach; ?>
                <button type="submit">Enregistrer</button>
            <?php else: ?>
                <p>Aucune page n'est disponible pour la configuration.</p>
            <?php endif; ?>
        </form>
</section>

</main>
</body>
</html>
