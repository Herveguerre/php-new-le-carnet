<?php
// Chemin vers le fichier JSON contenant les utilisateurs
$usersFile = './data/users.json';

// Fonction pour lire les utilisateurs existants
function getUsers($filePath) {
    if (file_exists($filePath)) {
        return json_decode(file_get_contents($filePath), true);
    }
    return [];
}

// Fonction pour ajouter un nouvel utilisateur
function addUser($filePath, $newUser) {
    $users = getUsers($filePath);
    $users[] = $newUser;
    file_put_contents($filePath, json_encode($users, JSON_PRETTY_PRINT));
}

// Message d'erreur ou succès
$message = "";

// Vérification de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    // Validation des champs
    if (empty($username) || empty($password) || empty($confirmPassword) || empty($email) || empty($phone)) {
        $message = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "L'adresse e-mail n'est pas valide.";
    } elseif (!preg_match('/^\+?\d{10,15}$/', $phone)) {
        $message = "Le numéro de téléphone doit contenir entre 10 et 15 chiffres.";
    } elseif ($password !== $confirmPassword) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {
        $users = getUsers($usersFile);

        // Vérifier si le nom d'utilisateur ou l'email existe déjà
        $userExists = false;
        foreach ($users as $user) {
            if ($user['username'] === $username || $user['email'] === $email) {
                $userExists = true;
                break;
            }
        }

        if ($userExists) {
            $message = "Ce nom d'utilisateur ou cette adresse e-mail est déjà pris.";
        } else {
            // Ajouter le nouvel utilisateur
            $newUser = [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'email' => $email,
                'phone' => $phone,
                'role' => 'user'
            ];
            addUser($usersFile, $newUser);
            $message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="site de recttes super facile ">
    <title>Inscription</title>
    
    <link rel="stylesheet" href="./css/login.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include './includes/header.php'; ?>

    <main>
        <h1>Inscription</h1>
        <form method="POST" action="register.php">
            <div>
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="email">Adresse e-mail :</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="phone">Numéro de téléphone :</label>
                <input type="text" id="phone" name="phone" required>
            </div>
            <div>
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div>
                <button type="submit">S'inscrire</button>
            </div>
            <?php if (!empty($message)) : ?>
                <p class="message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
        </form>
    </main>

    <?php include './includes/footer.php'; ?>
</body>
</html>
