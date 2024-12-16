<?php
session_start();
$dataFile = './data/users.json';
$dataFile = './data/data.json';
// Charger les styles
$style_file = './css/custom_styles.json';
$custom_styles = file_exists($style_file) ? json_decode(file_get_contents($style_file), true) : [];

// Assurez-vous que la clé `services` existe
if (!isset($data['services']) || !is_array($data['services'])) {
    $data['services'] = [
        'title' => 'Nos services',
        'cards' => []
    ];
}
require_once 'includes/functions.php';

// Charger la configuration du site
$config = getSiteConfig();
$dataFile = './data/data.json';
$data = json_decode(file_get_contents('./data/data.json'), true);

// Afficher un message si redirigé après suppression
if (isset($_GET['message']) && $_GET['message'] === 'deleted') {
    echo "<p style='color: red;'>Votre compte a été supprimé avec succès.</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="site de recttes super facile ">
    <title><?php echo $data['site_name']; ?></title>
    <link rel="icon" href="/G.png" type="image/x-icon" />  
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/index.css">
    
 
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include './includes/header.php'; ?>
    <h1>
    
    <main>       
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
    <?php if ($config['galerie_enabled'] ?? false): ?>
                <!-- Inclure le slider ici -->
     <?php include './includes/slider.php'; ?>
            <?php endif; ?>
     
    <link rel="stylesheet" href="./css/services.css">
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
