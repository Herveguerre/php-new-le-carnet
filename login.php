<?php
session_start();

// Fichier contenant les utilisateurs
$usersFile = './data/users.json';

// Fonction pour récupérer les utilisateurs depuis un fichier JSON
function getUsers($filePath) {
    if (file_exists($filePath)) {
        return json_decode(file_get_contents($filePath), true);
    }
    return [];
}

// Message d'information pour l'utilisateur
$message = "";

// Si une requête POST est reçue (formulaire soumis)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Vérification des champs vides
    if (empty($username) || empty($password)) {
        $message = "Tous les champs sont obligatoires.";
    } else {
        // Chargement des utilisateurs
        $users = getUsers($usersFile);
        $authenticated = false;

        foreach ($users as $user) {
            // Vérification des identifiants
            if ($user['username'] === $username && password_verify($password, $user['password'])) {
                $authenticated = true;

                // Stockage des informations utilisateur dans la session
                $_SESSION['user'] = [
                    'username' => $user['username'],
                    'role' => $user['role']
                ];

                // Redirection en fonction du rôle
                if ($user['role'] === 'admin') {
                    header('Location: admin/admin.php');
                } else {
                    header('Location: index.php');
                }
                exit; // Arrêter l'exécution après la redirection
            }
        }

        // Si les identifiants sont incorrects
        if (!$authenticated) {
            $message = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    }
}

// Vérification de la session utilisateur
if (isset($_SESSION['user'])) {
    $welcomeMessage = "Bienvenue, " . htmlspecialchars($_SESSION['user']['username']) . " (" . htmlspecialchars($_SESSION['user']['role']) . ").";
} else {
    $welcomeMessage = "Vous n'êtes pas connecté.";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
    <link rel="stylesheet" href="./css/login.css">
    <link rel="stylesheet" href="./css/style.css">

    <title>Connexion</title>
</head>
<body>
    <?php include './includes/header.php'; ?>
    <main>
        <h1>Connexion</h1>

        <!-- Affichage du message de bienvenue -->
        <p><?php echo $welcomeMessage; ?></p>

        <form method="POST" action="login.php">
            <div>
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <button type="submit">Se connecter</button>
                <p>Pas encore inscrit ? <a href="register.php">S'inscrire</a>.</p><p>Mot de passe oublíé ? <a href="forgot_password.php">Cliquez ici</a> pour obtenir un nouveau mot</p>
            </div>
            <!-- Affichage des messages d'erreur -->
            <?php if (!empty($message)) : ?>
                <p class="message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
        </form>

        <!-- Débogage de la session utilisateur (uniquement pour le développement) -->
        <pre>
            <?php print_r($_SESSION); ?>
        </pre>
    </main>
    <?php include './includes/footer.php'; ?>
</body>
</html>
