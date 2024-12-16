<?php
session_start();

// Fichier contenant les styles
$style_file = '../css/custom_styles.json';

// Définir les styles par défaut
function getDefaultStyles() {
    return [
        'title-color' => '#262424',
        'dark-color' => '#828181',
        'light-color' => '#f4f4f4',
        'header-bg-color' => 'linear-gradient(90deg, #007bff, #6610f2)',
        'footer-bg-color' => 'linear-gradient(90deg, #007bff, #6610f2)',
        'footer-border-top' => '#007bff',
        'body-bg-color' => 'linear-gradient(to right, #f8f9fa, #e9ecef)',
        'hero-bg-color' => 'linear-gradient(to bottom, #007bff, #0056b3)',
    ];
}

// Charger les styles existants ou par défaut
$default_styles = getDefaultStyles();
$custom_styles = file_exists($style_file) ? json_decode(file_get_contents($style_file), true) : $default_styles;

// Enregistrer les styles via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset'])) {
        $custom_styles = $default_styles; // Réinitialiser
    } else {
        foreach ($custom_styles as $key => $value) {
            $custom_styles[$key] = $_POST[$key] ?? $value;
        }
    }
    // Sauvegarder les styles mis à jour
    file_put_contents($style_file, json_encode($custom_styles, JSON_PRETTY_PRINT));
    header('Location: admin_style.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des Styles</title>
    <style>
        :root {
            <?php foreach ($custom_styles as $key => $value): ?>
                --<?= htmlspecialchars($key) ?>: <?= htmlspecialchars($value) ?>;
            <?php endforeach; ?>
        }
        body {
            font-family: Arial, sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
        }
        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        form label {
            display: block;
            margin: 10px 0 5px;
        }
        form input[type="color"],
        form input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        form button.reset {
            background-color: #dc3545;
        }
        .button {
            padding: 10px 20px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            background: #007bff;
            color: #fff;
            border-radius: 5px;
        }
        .button:hover {
            background: #0056b3;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gradient-picker/1.0.0/gradient-picker.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/gradient-picker/1.0.0/gradient-picker.min.css">
</head>
<body><?php include 'admin_header.php'; ?>
    <main>
        <h1>Gestion des Styles</h1><a class="button" href="background_picker.php">Genérer un gradient</a>
        <form method="POST">
            <?php foreach ($custom_styles as $key => $value): ?>
                <label for="<?= htmlspecialchars($key) ?>"><?= ucfirst(str_replace('-', ' ', $key)) ?></label>
                <?php if (strpos($value, 'linear-gradient') !== false): ?>
                    <!-- Gradient picker -->
                    <input   type="text" id="<?= htmlspecialchars($key) ?>" name="<?= htmlspecialchars($key) ?>" 
                           value="<?= htmlspecialchars($value) ?>" placeholder="Entrez un gradient CSS">
                <?php else: ?>
                    <!-- Color picker -->
                    <input style="height: 50px; " type="color" id="<?= htmlspecialchars($key) ?>" name="<?= htmlspecialchars($key) ?>" 
                           value="<?= htmlspecialchars($value) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <button type="submit" name="save">Sauvegarder</button>
            <button type="submit" name="reset" class="reset">Réinitialiser</button>
        </form>
    </main>
    <script>
        document.querySelectorAll('input[type="text"]').forEach(function (input) {
            const picker = new GradientPicker(input, {
                change: function (gradient) {
                    input.value = gradient;
                }
            });
        });
    </script>
</body>
</html>

