<?php
session_start();
require_once '../includes/functions.php';
// Vérification de l'accès administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Charger les données
$dataFile = '../data/data.json';
$data = json_decode(file_get_contents($dataFile), true);

// Gestion du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['site_name'] = htmlspecialchars($_POST['site_name']);
    $data['email'] = htmlspecialchars($_POST['email']);
    $data['address'] = htmlspecialchars($_POST['address']);
    $data['phone'] = htmlspecialchars($_POST['phone']);
    $data['opening_hours'] = htmlspecialchars($_POST['opening_hours']);

    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    $success = "Les informations ont été mises à jour avec succès.";
}




// Gestion de l'activation des options
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['user']|| $_SESSION['user']['role'] !== 'admin') {
        $data['email_enabled'] = isset($_POST['email_enabled']);
        $data['adresse_enabled'] = isset($_POST['adresse_enabled']);
        $data['phone_enabled'] = isset($_POST['phone_enabled']);
        
    } else {
        $error = "vous ne pouvez pas activer cette option.";
    }

    
    $data['email'] = htmlspecialchars($_POST['email']);
    $data['adresse'] = htmlspecialchars($_POST['address']);
    $data['phone'] = htmlspecialchars($_POST['phone']);

    

    
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    
    
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Accueil</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin_produits.css">
</head>
<body>
<?php include 'admin_header.php'; ?>

<main>
    <h1>Gestion des informations de l'accueil</h1>
    <p>les elements seront afficher automatiquement dans le pied de page ainsi que dans la page contact.</p>

    <?php if (isset($success)): ?>
        <p class="success"><?= $success ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="site_name">Nom du site :</label>
        <input type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($data['site_name']) ?>" required>

        

        <label for="email">Adresse e-mail :</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" required>
        <label for="email_enabled">Afficher l'e-mail dans le hero de la page d'accueil</label>
            <input type="checkbox" name="email_enabled" id="email_enabled" <?= ($data['email_enabled'] ?? false) ? 'checked' : ''; ?>>


        <label for="address">Adresse postale :</label>
        <textarea style="resize: none; width: 100%; " id="address" name="address" rows="3" required><?= htmlspecialchars($data['address']) ?></textarea>
        <label for="adresse_enabled">Afficher l'adresse dans le hero de la page d'accueil</label>
            <input type="checkbox" name="adresse_enabled" id="adresse_enabled" <?= ($data['adresse_enabled'] ?? false) ? 'checked' : ''; ?>>


        <label for="phone">Téléphone :</label>
        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($data['phone']) ?>" required>
        <label for="phone_enabled">Afficher le numéro de Téléphone dans le hero de la page d'accueil</label>
            <input type="checkbox" name="phone_enabled" id="phone_enabled" <?= ($data['phone_enabled'] ?? false) ? 'checked' : ''; ?>>


        <label for="opening_hours">Horaires d'ouverture :</label>
        <textarea style="resize: none; width: 30%; " id="opening_hours" name="opening_hours" rows="3"><?= htmlspecialchars($data['opening_hours'] ?? '') ?></textarea>

        <button type="submit">Enregistrer</button>
    </form>
<style>
    .hero {
    text-align: center;
    padding: 50px 20px;
    background: linear-gradient(to bottom, #007bff, #0056b3);
    color: white;
    animation: fadeIn 2s ease-in-out;
    margin: 20px auto;
}

.hero h2 {
    margin: 0;
    font-size: 2.5rem;
}

.hero .opening-hours {
    font-style: italic;
    margin-top: 20px;
}
</style><h3>Apercu de l'accueil </h3>
    <section class="hero">
        <h2 style="color: #333;" >Bienvenue sur <?= htmlspecialchars($data['site_name']) ?></h2>

        <?php if ($config['adresse_enabled'] ?? false): ?>
            <p>Adresse : <?= htmlspecialchars($data['address']) ?></p> 
        <?php endif; ?> 
    

        <?php if ($config['email_enabled'] ?? false): ?>
            <p>Email : <?= htmlspecialchars($data['email']) ?></p>
        <?php endif; ?> 

        <?php if ($config['phone_enabled'] ?? false): ?>
            <p>Téléphone : <?= htmlspecialchars($data['phone']) ?></p>
        <?php endif; ?> 


        <?php if (!empty($data['opening_hours'])): ?>
            <p>Horaires d'ouverture :</p>
            <p class="opening-hours"><?= nl2br(htmlspecialchars($data['opening_hours'])) ?></p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
