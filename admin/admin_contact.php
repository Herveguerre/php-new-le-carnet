<?php
session_start();

// Vérification du rôle admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Chargement des messages
$contactFile = '../data/messages.json';
$messages = file_exists($contactFile) ? json_decode(file_get_contents($contactFile), true) : [];

// Suppression d'un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = htmlspecialchars($_POST['delete_id']);

    // Recherche et suppression du message
    $messages = array_filter($messages, function ($message) use ($deleteId) {
        return $message['id'] !== $deleteId;
    });

    // Sauvegarde dans le fichier JSON
    file_put_contents($contactFile, json_encode(array_values($messages), JSON_PRETTY_PRINT));
    $deleteMessage = "Message supprimé avec succès.";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Messages Contact</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <main>
        <h1>Messages reçus</h1>

        <?php if (isset($deleteMessage)): ?>
            <p class="success-message"><?= $deleteMessage ?></p>
        <?php endif; ?>

        <?php if (empty($messages)): ?>
            <p>Aucun message pour le moment.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td><?= htmlspecialchars($msg['id']) ?></td>
                            <td><?= htmlspecialchars($msg['nom']) ?></td>
                            <td><?= htmlspecialchars($msg['email']) ?></td>
                            <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
                            <td><?= htmlspecialchars($msg['date']) ?></td>
                            <td>
                                <form action="admin_contact.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="delete_id" value="<?= htmlspecialchars($msg['id']) ?>">
                                    <button type="submit" class="delete-button">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>
