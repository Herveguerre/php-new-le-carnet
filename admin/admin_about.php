<?php
session_start();

// Vérification du rôle (admin uniquement)
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$dataFile = '../data/about.json';
$uploadDir = '../assets/about/';

// Chargement des données
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([], JSON_PRETTY_PRINT));
}
$aboutData = json_decode(file_get_contents($dataFile), true);

// Création du dossier pour les images si nécessaire
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Trier les sections par position
usort($aboutData, fn($a, $b) => $a['position'] <=> $b['position']);

// Gestion des actions CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';

    if ($type === 'add' || $type === 'edit') {
        // Gestion d'une section
        $section = $_POST['section'] ?? '';
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        // Gestion de l'image
        $imageName = $_POST['existing_image'] ?? '';
        if (!empty($_FILES['image']['name'])) {
            $imageName = time() . '-' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }

        if ($type === 'add') {
            $aboutData[] = [
                'section' => $section,
                'title' => $title,
                'content' => $content,
                'image' => $imageName,
                'members' => [],
                'position' => count($aboutData)
            ];
        } elseif ($type === 'edit') {
            $index = (int) $_POST['index'];
            $aboutData[$index] = [
                'section' => $section,
                'title' => $title,
                'content' => $content,
                'image' => $imageName,
                'members' => $aboutData[$index]['members'], // Conserver les membres existants
                'position' => $aboutData[$index]['position']
            ];
        }
    }

    if ($type === 'delete') {
        $index = (int) $_POST['index'];
        if (!empty($aboutData[$index]['image'])) {
            unlink($uploadDir . $aboutData[$index]['image']);
        }
        unset($aboutData[$index]);
        $aboutData = array_values($aboutData); // Réindexer
    }

    if ($type === 'add_member' || $type === 'edit_member') {
        // Gestion des membres d'une section
        $index = (int) $_POST['index'];
        $name = $_POST['name'] ?? '';
        $role = $_POST['role'] ?? '';
        $memberImage = $_POST['existing_member_image'] ?? '';

        if (!empty($_FILES['member_photo']['name'])) {
            $memberImage = time() . '-' . basename($_FILES['member_photo']['name']);
            move_uploaded_file($_FILES['member_photo']['tmp_name'], $uploadDir . $memberImage);
        }

        if ($type === 'add_member') {
            $aboutData[$index]['members'][] = [
                'name' => $name,
                'role' => $role,
                'photo' => $memberImage
            ];
        } elseif ($type === 'edit_member') {
            $memberIndex = (int) $_POST['member_index'];
            $aboutData[$index]['members'][$memberIndex] = [
                'name' => $name,
                'role' => $role,
                'photo' => $memberImage
            ];
        }
    }

    if ($type === 'delete_member') {
        $index = (int) $_POST['index'];
        $memberIndex = (int) $_POST['member_index'];

        if (!empty($aboutData[$index]['members'][$memberIndex]['photo'])) {
            unlink($uploadDir . $aboutData[$index]['members'][$memberIndex]['photo']);
        }

        unset($aboutData[$index]['members'][$memberIndex]);
        $aboutData[$index]['members'] = array_values($aboutData[$index]['members']); // Réindexer
    }

    if ($type === 'move') {
        // Déplacement des sections
        $index = (int) $_POST['index'];
        $direction = $_POST['direction'];

        if ($direction === 'up' && $index > 0) {
            $aboutData[$index]['position']--;
            $aboutData[$index - 1]['position']++;
        } elseif ($direction === 'down' && $index < count($aboutData) - 1) {
            $aboutData[$index]['position']++;
            $aboutData[$index + 1]['position']--;
        }

        usort($aboutData, fn($a, $b) => $a['position'] <=> $b['position']);
    }

    // Sauvegarde des données
    file_put_contents($dataFile, json_encode($aboutData, JSON_PRETTY_PRINT));
    header('Location: admin_about.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion - À propos</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        img { width: 100px; }
        .section-preview { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; }
        button { margin: 5px; }
        .edit-form { display: none; }
    </style>
    <script>
        function openEditForm(index) {
            document.querySelectorAll('.edit-form').forEach(form => form.style.display = 'none');
            document.getElementById('edit-form-' + index).style.display = 'block';
        }
    </script>
</head>
<body>
    <?php include 'admin_header.php'; ?>
    <main>
        <h1>Gestion de la page "À propos"</h1>

        <!-- Formulaire d'ajout -->
        <form action="admin_about.php" method="POST" enctype="multipart/form-data">
            <h2>Ajouter une section</h2>
            <input type="hidden" name="type" value="add">
            <label for="section">Section :</label>
            <select name="section" required>
                <option value="presentation">Présentation</option>
                <option value="activity">Activité</option>
                <option value="team">Équipe</option>
            </select>
            <label for="title">Titre :</label>
            <input type="text" name="title" required>
            <label for="content">Contenu :</label>
            <textarea name="content" rows="5" required></textarea>
            <label for="members">Membres :</label>
            <label for="image">Image :</label>
            <input type="file" name="image" accept="image/*">
            <button type="submit">Ajouter</button>
        </form>

        <!-- Aperçu des sections existantes -->
        <h2>Sections existantes</h2>
        <?php foreach ($aboutData as $index => $section): ?>
        <div class="section-preview">
            <h3><?= htmlspecialchars($section['title']) ?> (<?= htmlspecialchars($section['section']) ?>)</h3>
            <p><?= nl2br(htmlspecialchars($section['content'])) ?></p>
            
            <?php if (!empty($section['image'])): ?>
                <img src="<?= $uploadDir . htmlspecialchars($section['image']) ?>" alt="Image">
            <?php endif; ?>

            <!-- Membres -->
            <h4>Membres</h4>
            <ul>
                <?php foreach ($section['members'] as $memberIndex => $member): ?>
                    <li>
                        <strong><?= htmlspecialchars($member['name']) ?></strong> - <?= htmlspecialchars($member['role']) ?>
                        <?php if (!empty($member['photo'])): ?>
                            <img src="<?= $uploadDir . htmlspecialchars($member['photo']) ?>" alt="Photo">
                        <?php endif; ?>
                        <form action="admin_about.php" method="POST" style="display:inline;">
                            <input type="hidden" name="type" value="delete_member">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <input type="hidden" name="member_index" value="<?= $memberIndex ?>">
                            <button type="submit">Supprimer</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>

        <!-- Boutons de déplacement -->

            <!-- Ajouter un membre -->
            <form action="admin_about.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="type" value="add_member">
                <input type="hidden" name="index" value="<?= $index ?>">
                <label>Nom :</label>
                <input type="text" name="name" required>
                <label>Rôle :</label>
                <input type="text" name="role" required>
                <label>Photo :</label>
                <input type="file" name="member_photo" accept="image/*">
                <button type="submit">Ajouter un membre</button>
            </form>
        </div>


        <?php endforeach; ?>

        
    </main>
</body>
</html>
