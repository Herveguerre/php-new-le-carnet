<?php
session_start();

// Vérification du rôle (admin uniquement)
if (!isset($_SESSION['user']) || !is_array($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // Redirection vers la page de connexion
    header('Location: ../login.php');
    exit();
}

// Chemin vers le fichier JSON des utilisateurs
$usersFile = '../data/users.json';

// Fonction pour lire les utilisateurs
function getUsers($filePath) {
    if (file_exists($filePath)) {
        return json_decode(file_get_contents($filePath), true);
    }
    return [];
}

// Fonction pour sauvegarder les utilisateurs
function saveUsers($filePath, $users) {
    file_put_contents($filePath, json_encode($users, JSON_PRETTY_PRINT));
}

// Chargement des utilisateurs
$users = getUsers($usersFile);

// Traitement des actions (ajout, suppression, modification)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete' && isset($_POST['username'])) {
            // Récupération de l'utilisateur ciblé
            $usernameToDelete = $_POST['username'];
            $userToDelete = array_filter($users, function ($user) use ($usernameToDelete) {
                return $user['username'] === $usernameToDelete;
            });

            // Vérification : ne pas supprimer un admin
            if (!empty($userToDelete) && current($userToDelete)['role'] === 'admin') {
                $message = "Vous ne pouvez pas supprimer un administrateur.";
            } else {
                // Suppression de l'utilisateur
                $users = array_filter($users, function ($user) use ($usernameToDelete) {
                    return $user['username'] !== $usernameToDelete;
                });
                saveUsers($usersFile, $users);
                $message = "Utilisateur supprimé avec succès.";
            }
        } elseif ($_POST['action'] === 'add') {
            // Ajout d'un utilisateur
            $newUsername = trim($_POST['new_username']);
            $newPassword = trim($_POST['new_password']);
            $newRole = $_POST['new_role'];

            if (!empty($newUsername) && !empty($newPassword)) {
                $users[] = [
                    'username' => $newUsername,
                    'password' => password_hash($newPassword, PASSWORD_BCRYPT),
                    'role' => $newRole
                ];
                saveUsers($usersFile, $users);
                $message = "Nouvel utilisateur ajouté avec succès.";
            } else {
                $message = "Tous les champs sont obligatoires pour ajouter un utilisateur.";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include 'admin_header.php'; ?>

    <main>
        <h1>Gestion des utilisateurs</h1>

        <!-- Ajouter un utilisateur -->
        <section>
            <h2>Ajouter un utilisateur</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div>
                    <label for="new_username">Nom d'utilisateur :</label>
                    <input type="text" id="new_username" name="new_username" required>
                </div>
                <div>
                    <label for="new_password">Mot de passe :</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div>
                    <label for="new_role">Rôle :</label>
                    <select id="new_role" name="new_role">
                        <option value="user">Utilisateur</option>
                        <option style="color: red;" value="admin">Administrateur</option>
                    </select>
                </div>
                <button type="submit">Ajouter</button>
            </form>
        </section>

        <!-- Message de confirmation -->
        <?php if (isset($message)) : ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Liste des utilisateurs -->
        <section>
            <h2>Liste des utilisateurs</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nom d'utilisateur</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td>
                                <?php if ($user['role'] !== 'admin') : ?>
                                    <!-- Supprimer un utilisateur -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                                        <button type="submit" onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');">Supprimer</button>
                                    </form>
                                <?php else : ?>
                                    <span class="disabled-action">Non modifiable</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </section>

       
    </main>
</body>
</html>
