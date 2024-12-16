<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateur de Dégradés CSS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #ffffff; /* Par défaut */
            color: #333;
            margin: 0;
            padding: 0;
        }
        #app {
            
            padding: 20px;
        } 
        
        .gradient-preview {
            width: 100%;
            height: 300px;
            margin: 20px auto ;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: linear-gradient(90deg, #020024 0%, #090979 35%, #00d4ff 100%);
        }
        .controls {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        .stop-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        input, select, button {
            padding: 10px;
            font-size: 16px;
        }
        button {
            border: none;
            cursor: pointer;
            background: #007bff;
            color: #fff;
            border-radius: 5px;
        }
        button:hover {
            background: #0056b3;
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
        #cssCode {
            width: 90%;
            padding: 10px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #f9f9f9;
            resize: none;
        }
        #copyBtn {
            margin-top: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>
    <main>
    <div id="app">
        <h1>Générateur de Dégradés CSS</h1>
        <a class="button" href="admin_style.php">Retour au style </a>
        <!-- Aperçu du dégradé -->
        <div id="gradientPreview" class="gradient-preview"></div>

        <!-- Contrôles pour le dégradé -->
        <div class="controls">
            <div class="stop-wrapper">
                <label>Direction :</label>
                <select id="direction">
                    <option value="90deg">Horizontal (90°)</option>
                    <option value="180deg">Vertical (180°)</option>
                    <option value="45deg">Diagonal (45°)</option>
                </select>
            </div>
            <div class="stop-wrapper">
                <label>Couleur 1 :</label>
                <input style="height: 50px; width: 50px;"   type="color" id="color1" value="#020024">
                <input   type="number" id="stop1" value="0" min="0" max="100"> %
            </div>
            <div class="stop-wrapper">
                <label>Couleur 2 :</label>
                <input style="height: 50px; width: 50px;"  type="color" id="color2" value="#090979">
                <input type="number" id="stop2" value="35" min="0" max="100"> %
            </div>
            <div class="stop-wrapper">
                <label>Couleur 3 :</label>
                <input style="height: 50px; width: 50px;"  type="color" id="color3" value="#00d4ff">
                <input type="number" id="stop3" value="100" min="0" max="100"> %
            </div>
            <button id="applyGradient">Appliquer</button>
        </div>

        <!-- Zone pour afficher le code CSS -->
        <h3>Code CSS :</h3>
        <textarea id="cssCode" rows="3" readonly></textarea>
        <button id="copyBtn">Copier le Code</button>
        
    </div>
    
    </main>
    

    <script>
        const gradientPreview = document.getElementById("gradientPreview");
        const directionSelect = document.getElementById("direction");
        const color1Input = document.getElementById("color1");
        const stop1Input = document.getElementById("stop1");
        const color2Input = document.getElementById("color2");
        const stop2Input = document.getElementById("stop2");
        const color3Input = document.getElementById("color3");
        const stop3Input = document.getElementById("stop3");
        const applyGradientBtn = document.getElementById("applyGradient");
        const cssCodeArea = document.getElementById("cssCode");
        const copyBtn = document.getElementById("copyBtn");

        // Fonction pour générer et appliquer le dégradé
        function applyGradient() {
            const direction = directionSelect.value;
            const color1 = color1Input.value;
            const stop1 = stop1Input.value;
            const color2 = color2Input.value;
            const stop2 = stop2Input.value;
            const color3 = color3Input.value;
            const stop3 = stop3Input.value;

            // Création de la valeur du dégradé CSS
            const gradientCSS = `linear-gradient(${direction}, ${color1} ${stop1}%, ${color2} ${stop2}%, ${color3} ${stop3}%)`;

            // Application au background
            gradientPreview.style.background = gradientCSS;

            // Affichage dans le champ texte
            cssCodeArea.value = `background: ${gradientCSS};`;
        }

        // Fonction pour copier le texte CSS
        function copyToClipboard() {
            cssCodeArea.select();
            document.execCommand("copy");
            alert("Code copié dans le presse-papier !");
        }

        // Appliquer le dégradé au clic sur le bouton
        applyGradientBtn.addEventListener("click", applyGradient);

        // Bouton de copie
        copyBtn.addEventListener("click", copyToClipboard);

        // Appliquer par défaut au chargement
        applyGradient();
    </script>
</body>
</html>
