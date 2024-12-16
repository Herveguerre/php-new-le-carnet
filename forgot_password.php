<?php
session_start();

// Chemin vers le fichier JSON contenant les utilisateurs
$usersFile = './data/users.json';

// Fonction pour charger les utilisateurs
function getUsers($filePath) {
    if (file_exists($filePath)) {
        return json_decode(file_get_contents($filePath), true);
    }
    return [];
}

// Fonction pour mettre à jour les utilisateurs
function updateUsers($filePath, $users) {
    file_put_contents($filePath, json_encode($users, JSON_PRETTY_PRINT));
}

// Fonction pour générer un mot de passe aléatoire
function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    return substr(str_shuffle($chars), 0, $length);
}

// Initialisation des variables
$message = "";
$email = "";
$phone = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $users = getUsers($usersFile);
    $userFound = null;

    // Recherche de l'utilisateur par e-mail ET numéro de téléphone
    foreach ($users as &$user) {
        if ($user['email'] === $email && $user['phone'] === $phone) {
            $userFound = &$user;
            break;
        }
    }

    if ($userFound) {
        // Générer un nouveau mot de passe
        $newPassword = generatePassword();

        // Mettre à jour le mot de passe de l'utilisateur
        $userFound['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        updateUsers($usersFile, $users);

        // Envoyer le nouveau mot de passe
        $to = $userFound['email'];
        $subject = "Réinitialisation de votre mot de passe";
        $body = "Bonjour {$userFound['username']},\n\nVotre nouveau mot de passe est : $newPassword\n\nVeuillez le changer après connexion.";
        $headers = "From: noreply@example.com";

        // Envoi de l'e-mail
        if (mail($to, $subject, $body, $headers)) {
            $message = "Un nouveau mot de passe a été envoyé à votre adresse e-mail.";
        } else {
            $message = "Erreur lors de l'envoi de l'e-mail.";
        }
    } else {
        $message = "Aucun utilisateur trouvé avec cet e-mail et ce numéro de téléphone.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="./css/login.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include './includes/header.php'; ?>
    <main>
        <h1>Réinitialisation du mot de passe</h1>
        <form method="POST" action="forgot_password.php">
            <div>
                <label for="email">E-mail :</label>
                <input type="email" id="email" name="email" 
                       placeholder="Entrez votre e-mail" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div>
                <label for="phone">Numéro de téléphone :</label>
                <input type="tel" id="phone" name="phone" 
                       placeholder="Entrez votre numéro de téléphone" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
            <div>
                <button type="submit">Réinitialiser</button>
            </div>
            <?php if (!empty($message)) : ?>
                <p class="message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
        </form>
    </main>
    <?php include './includes/footer.php'; ?>
</body>
</html>
