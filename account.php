<?php
ob_start();
session_start();


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// Vérification de la session utilisateur
// if (!isset($_SESSION['user'])) {
//     echo "<p>Vous n'êtes pas connecté.</p>";
//     exit;
// } else {
//     echo "<p>Bienvenue, " . htmlspecialchars($_SESSION['user']['username']) . ".</p>";
//     echo "<p>Rôle : " . htmlspecialchars($_SESSION['user']['role']) . ".</p>";
// }

// Désactiver les warnings inutiles pour éviter des sorties avant les headers
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Chemin vers le fichier JSON contenant les utilisateurs
$usersFile = './data/users.json';

// Fonction pour lire les utilisateurs existants
function getUsers($filePath) {
    if (file_exists($filePath)) {
        return json_decode(file_get_contents($filePath), true);
    }
    return [];
}

// Fonction pour mettre à jour un utilisateur
function updateUser($filePath, $updatedUser) {
    $users = getUsers($filePath);
    foreach ($users as &$user) {
        if ($user['username'] === $updatedUser['username']) {
            $user = $updatedUser;
            break;
        }
    }
    file_put_contents($filePath, json_encode($users, JSON_PRETTY_PRINT));
}

// Chargement de l'utilisateur connecté
$currentUser = null;
$users = getUsers($usersFile);
foreach ($users as $user) {
    if ($user['username'] === $_SESSION['user']['username']) {
        $currentUser = $user;
        break;
    }
}

// Message d'information
$message = "";

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    // Validation des champs
    if (empty($email) || empty($phone)) {
        $message = "L'email et le numéro de téléphone sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Adresse e-mail invalide.";
    } elseif (!empty($password) && $password !== $confirmPassword) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {
        // Mise à jour des informations utilisateur
        $updatedUser = [
            'username' => $currentUser['username'],
            'password' => !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : $currentUser['password'],
            'email' => $email,
            'phone' => $phone,
            'role' => $currentUser['role']
        ];
        updateUser($usersFile, $updatedUser);

        // Mise à jour des informations dans la session
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['phone'] = $phone;

        $message = "Informations mises à jour avec succès.";
        $currentUser = $updatedUser; // Mise à jour des données affichées
    }
}



// Fonction pour supprimer un utilisateur du fichier JSON
function deleteUser($filePath, $username) {
    $users = getUsers($filePath); // Charger les utilisateurs depuis le fichier JSON

    foreach ($users as $key => $user) {
        if ($user['username'] === $username) {
            unset($users[$key]); // Supprime l'utilisateur correspondant
            break;
        }
    }

    // Réécrire le fichier JSON avec les utilisateurs restants
    file_put_contents($filePath, json_encode(array_values($users), JSON_PRETTY_PRINT));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    if (isset($_SESSION['user']['username'])) {
        $username = $_SESSION['user']['username'];

        // Vérifier si c'est l'administrateur
        if ($userrole === 'admin')  { // Remplacez 'admin' par le nom ou identifiant de l'administrateur
            $message = "Erreur : Impossible de supprimer un compte administrateur.";
        } else {
            deleteUser($usersFile, $username);

            // Détruire la session
            session_unset();
            session_destroy();

            // Redirection
            header("Location: index.php?message=deleted");
            exit;
        }
    } else {
        $message = "Erreur : impossible de trouver votre compte.";
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon compte</title>
   <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/login.css">
    
    
</head>
<body>
    <?php include './includes/header.php'; ?>

    <main>
        <h1>Mon compte</h1>
        <p>Modifier vos informations personnelles :</p>

        <form method="POST" action="account.php">
            <div>
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" value="<?= htmlspecialchars($currentUser['username'] ?? '') ?>" disabled>
            </div>
            <div>
                <label for="email">Adresse e-mail :</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" required>
            </div>
            <div>
                <label for="phone">Numéro de téléphone :</label>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" required>
            </div>
            <div>
                <label for="password">Nouveau mot de passe :</label>
                <input type="password" id="password" name="password">
            </div>
            <div>
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
            <div>
                <button type="submit">Mettre à jour</button>
            </div>
            <?php if (!empty($message)) : ?>
                <p class="message"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
        </form>
        <form method="POST" action="account.php">
            <button type="submit" name="delete_account" style="background-color: red; color: white; border: none; padding: 10px 15px; cursor: pointer;">
                Supprimer mon compte
            </button>
        </form>


    </main>

    <?php include './includes/footer.php'; ?>
</body>
</html>
