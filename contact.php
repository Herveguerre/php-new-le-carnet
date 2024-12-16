<?php
session_start();

// Déclaration du fichier JSON pour stocker les messages
$contactFile = './data/messages.json';
$contactMessage = "";

// Chargement des messages existants
$messages = file_exists($contactFile) ? json_decode(file_get_contents($contactFile), true) : [];

// Gestion du formulaire de contact
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données
    $nom = htmlspecialchars($_POST['nom'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars($_POST['message'] ?? '');

    // Validation des champs
    if ($nom && $email && $message) {
        // Création d'un message
        $newMessage = [
            'id' => uniqid(), // Identifiant unique pour chaque message
            'nom' => $nom,
            'email' => $email,
            'message' => $message,
            'date' => date('d-m-Y H:i:s'),
        ];

        // Ajout du message au tableau des messages
        $messages[] = $newMessage;

        // Enregistrement dans le fichier JSON
        file_put_contents($contactFile, json_encode($messages, JSON_PRETTY_PRINT));

        // Confirmation d'envoi
        $contactMessage = "Votre message a bien été envoyé.";
    } else {
        $contactMessage = "Tous les champs sont obligatoires et l'email doit être valide.";
    }
}

// Préremplissage du nom pour les utilisateurs connectés
$userName = isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['username']) : '';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="site de recttes super facile ">
    <title>Contact</title>
    <link rel="stylesheet" href="./css/login.css">
    <link rel="stylesheet" href="./css/style.css">
    
</head>
<body>
    <?php include './includes/header.php'; ?>

    <main>
        <h1>Contactez-nous</h1>

        <?php if ($contactMessage): ?>
            <p class="message"><?= $contactMessage ?></p>
        <?php endif; ?>

        <form action="contact.php" method="POST">
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" value="<?= $userName ?>" <?= $userName ? 'readonly' : '' ?> required>

            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required>

            <label for="message">Message :</label>
            <textarea id="message" name="message" rows="5" required></textarea>

            <button type="submit">Envoyer</button>
        </form>
    </main>

    <?php include './includes/footer.php'; ?>
</body>
</html>
