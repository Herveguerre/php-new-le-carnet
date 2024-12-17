<?php
ob_start();
session_start();
$dataFile = './data/users.json';
$dataFile = './data/data.json';

require_once 'includes/functions.php';


// Vérifier si le blog est activé
$config = getSiteConfig();
 if (!($config['blog_enabled'] ?? false)) {
    header('Location: /index.php');
    exit;
 }


// Charger les données des salons depuis le fichier JSON
$blogFile = 'data/blog.json';
$blogData = file_exists($blogFile) ? json_decode(file_get_contents($blogFile), true) : [];

// Si le fichier JSON est vide ou mal formé, initialiser les salons par défaut
if (empty($blogData) || !is_array($blogData)) {
    $salons = ['Salon1', 'Salon2', 'Salon3', 'Salon4', 'Salon5'];
    foreach ($salons as $salon) {
        $blogData[$salon] = [];
    }
    // Sauvegarder les salons par défaut dans le fichier JSON
    file_put_contents($blogFile, json_encode($blogData, JSON_PRETTY_PRINT));
} else {
    // Utiliser les clés du tableau $blogData comme noms des salons
    $salons = array_keys($blogData);
}

// Gestion de l'envoi d'un message
// Gestion de l'envoi d'un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salon'], $_POST['message'])) {
    // Vérifier si l'utilisateur est connecté
    if (isset($_SESSION['user']['username'])) {
        $username = $_SESSION['user']['username'];
    } else {
        header('Location: /login.php');
        exit;
    }

    // Récupérer les données du formulaire
    $salon = $_POST['salon'];
    $message = htmlspecialchars($_POST['message']);

    // Vérifier que le salon est valide et que le message n'est pas vide
    if (!empty($message) && in_array($salon, $salons)) {
        // Ajouter le message au salon sélectionné
        $blogData[$salon][] = [
            'username' => $username,
            'message' => $message,
            'validated' => false, // Les messages doivent être validés
            'timestamp' => time(),
        ];

        // Enregistrer les données mises à jour dans le fichier JSON
        file_put_contents($blogFile, json_encode($blogData, JSON_PRETTY_PRINT));

        // Ajouter un message de succès dans la session
        $_SESSION['success'] = "Votre message a été envoyé et sera visible après validation.";

        // Rediriger vers la même page pour éviter la répétition du POST
        header('Location: /blog.php');
        exit;
    } else {
        $error = "Erreur lors de l'envoi du message.";
    }
}


ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum</title>
    <link rel="stylesheet" href="css/blog.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php if (isset($_SESSION['success'])): ?>
    <p class="success"><?= $_SESSION['success'] ?></p>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($error)): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

    <main>
        <h1>Forum</h1>

        <?php if (isset($success)): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" class="blog-form">
            <label for="salon">Salon :</label>
            <select name="salon" id="salon" required>
                <?php foreach ($salons as $salon): ?>
                    <option value="<?= htmlspecialchars($salon) ?>"><?= htmlspecialchars($salon) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="message">Message :</label>
            <textarea name="message" id="message" rows="5" required></textarea>

            <button type="submit">Envoyer</button>
        </form>

        <section class="tabs">
            <?php foreach ($salons as $index => $salon): ?>
                
                <div class="tab <?= $index === 0 ? 'active' : '' ?>" data-tab="<?= $index ?>">
                    <?= htmlspecialchars($salon) ?>
                </div>
            <?php endforeach; ?>
        </section>
      
        <section class="tab-contents">
            <?php foreach ($salons as $index => $salon): ?>
                <div class="tab-content <?= $index === 0 ? 'active' : '' ?>" data-tab="<?= $index ?>">
                    <h2><?= htmlspecialchars($salon) ?></h2>
                    <ul>
                        <?php if (!empty($blogData[$salon])): ?>


                            <?php 
                            // Inverser l'ordre des messages pour afficher les derniers en premier
                            $reversedMessages = array_reverse($blogData[$salon]); 
                            ?>


                            <?php foreach ($reversedMessages as $msg): ?>
                                <?php if ($msg['validated']): ?>
                                    <li>
                                        <strong><?= htmlspecialchars($msg['username']) ?> :</strong>
                                        <?= htmlspecialchars($msg['message']) ?>
                                    <!--utile l 100-->
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Aucun message pour l'instant.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </section>


    </main>
    <?php include 'includes/footer.php'; ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelectorAll(".tab");
    const contents = document.querySelectorAll(".tab-content");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            const tabIndex = tab.getAttribute("data-tab");

            // Activer l'onglet sélectionné
            tabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");

            // Afficher le contenu correspondant
            contents.forEach(content => {
                content.classList.remove("active");
                if (content.getAttribute("data-tab") === tabIndex) {
                    content.classList.add("active");
                }
            });
        });
    });
});

    </script>
</body>
</html>
