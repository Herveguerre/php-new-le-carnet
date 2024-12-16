<?php
session_start();

// Vérification de l'accès administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Charger les données JSON
$dataFile = '../data/data.json';
$data = json_decode(file_get_contents($dataFile), true);

// Assurez-vous que la clé `services` existe
if (!isset($data['services']) || !is_array($data['services'])) {
    $data['services'] = [
        'title' => 'Nos services',
        'cards' => [
            [
                'title' => 'Service 1',
                'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.',
                'link' => '#'
            ],
            [
                'title' => 'Service 2',
                'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.',
                'link' => '#'
            ],
            [
                'title' => 'Service 3',
                'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.',
                'link' => '#'
            ],
        ]
    ];
}

// Modification du titre principal (h2)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_title') {
    $newTitle = $_POST['section_title'] ?? '';
    if ($newTitle) {
        $data['services']['title'] = htmlspecialchars($newTitle);
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    }
}

// Ajout d'une nouvelle carte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_card') {
    $newCardTitle = $_POST['card_title'] ?? '';
    $newCardDescription = $_POST['card_description'] ?? '';
    $newCardLink = $_POST['card_link'] ?? '#';
    if ($newCardTitle && $newCardDescription) {
        $data['services']['cards'][] = [
            'title' => htmlspecialchars($newCardTitle),
            'description' => htmlspecialchars($newCardDescription),
            'link' => htmlspecialchars($newCardLink)
        ];
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    }
}

// Modification d'une carte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_card') {
    $indexToEdit = $_POST['index'] ?? -1;
    $newCardTitle = $_POST['edit_card_title'] ?? '';
    $newCardDescription = $_POST['edit_card_description'] ?? '';
    $newCardLink = $_POST['edit_card_link'] ?? '';
    if (isset($data['services']['cards'][$indexToEdit]) && $newCardTitle && $newCardDescription) {
        $data['services']['cards'][$indexToEdit]['title'] = htmlspecialchars($newCardTitle);
        $data['services']['cards'][$indexToEdit]['description'] = htmlspecialchars($newCardDescription);
        $data['services']['cards'][$indexToEdit]['link'] = htmlspecialchars($newCardLink);
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    }
}

// Suppression d'une carte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_card') {
    $indexToDelete = $_POST['index'] ?? -1;
    if (isset($data['services']['cards'][$indexToDelete])) {
        array_splice($data['services']['cards'], $indexToDelete, 1);
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Nos Services</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include 'admin_header.php'; ?>
<main>
    <h2>Gestion de la section "Nos services"</h2>
    
    <!-- Modifier le titre principal -->
    <section>
        <h3>Modifier le titre principal</h3>
        <form action="" method="POST">
            <input type="hidden" name="action" value="edit_title">
            <div>
                <label for="section_title">Titre principal :</label>
                <input type="text" id="section_title" name="section_title" value="<?= htmlspecialchars($data['services']['title']) ?>" required>
            </div>
            <button type="submit">Modifier le titre</button>
        </form>
    </section>

    <!-- Ajouter une nouvelle carte -->
    <section>
    <h3>Ajouter une nouvelle carte</h3>
    <form action="" method="POST">
        <input type="hidden" name="action" value="add_card">
        <div>
            <label for="card_title">Titre de la carte :</label>
            <input type="text" id="card_title" name="card_title" required>
        </div>
        <div>
            <label for="card_description">Description :</label>
            <textarea id="card_description" name="card_description" rows="3" required></textarea>
        </div>
        <div>
            <label for="card_link">Lien :</label>
            <input type="url" id="card_link" name="card_link" placeholder="https://example.com" required>
        </div>
        <button type="submit">Ajouter la carte</button>
    </form>
</section>


    <!-- Liste des cartes existantes -->
    <section>
    <h3>Cartes existantes</h3>
    <ul>
        <?php foreach ($data['services']['cards'] as $index => $card): ?>
            <li>
                <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="edit_card">
                    <input type="hidden" name="index" value="<?= $index ?>">
                    <input type="text" name="edit_card_title" value="<?= htmlspecialchars($card['title']) ?>" required>
                    <textarea name="edit_card_description" rows="3" required><?= htmlspecialchars($card['description']) ?></textarea>
                    <input type="url" name="edit_card_link" value="<?= htmlspecialchars($card['link']) ?>" placeholder="https://example.com" required>
                    <button type="submit">Modifier</button>
                </form>
                <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="delete_card">
                    <input type="hidden" name="index" value="<?= $index ?>">
                    <button type="submit">Supprimer</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

</main>
</body>
</html>
