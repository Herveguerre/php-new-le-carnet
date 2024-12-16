<?php
session_start();
$dataFile = './data/users.json';

require_once 'includes/functions.php';
// Vérifier si la galerie est activée
$config = getSiteConfig();
if (!($config['galerie_enabled'] ?? false)) {
    header('Location: /index.php');
    exit;
}

// Charger les données des produits
$dataProduitsFile = './data/produits.json';
$dataProduits = file_exists($dataProduitsFile) ? json_decode(file_get_contents($dataProduitsFile), true) : [];
$produitsImages = $dataProduits['produits'] ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie Produits</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        /* Styles généraux */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            color: #333;
        }

        main {
            padding: 20px;
            text-align: center;
        }

        h1 {
            font-size: 2rem;
            color: #444;
            margin-bottom: 20px;
        }

        /* Conteneur principal de la galerie */
        .galerie-container {
            position: relative;
            max-width: 900px;
            margin: 20px auto;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            background: #fff;
        }

        /* Slider track */
        .slider-track {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

        /* Slide */
        .slide {
            flex: 0 0 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        /* Image des produits */
        .slide img {
            max-width: 100%;
            max-height: 400px;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .slide img:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }

        /* Titre du produit */
        .title {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 1rem;
            white-space: nowrap;
        }

        /* Boutons de navigation */
        .nav-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: #fff;
            border: none;
            border-radius: 50%;
            padding: 10px 15px;
            font-size: 1.5rem;
            cursor: pointer;
            transition: background 0.3s ease;
            z-index: 10;
        }

        .nav-button:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        .nav-prev {
            left: 10px;
        }

        .nav-next {
            right: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            h1 {
                font-size: 1.5rem;
            }

            .slide img {
                max-height: 250px;
            }

            .title {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include './includes/header.php'; ?>

    <main>
        <h1>Découvrez nos Produits</h1>
        <?php if (!empty($produitsImages)): ?>
            <div class="galerie-container">
                <div class="slider-track">
                    <?php foreach ($produitsImages as $produit): ?>
                        <div class="slide">
                            <a href="produit_details.php?id=<?= htmlspecialchars($produit['id']) ?>">
                                <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
                                <div class="title"><?= htmlspecialchars($produit['nom']) ?></div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Boutons de navigation -->
                <button class="nav-button nav-prev">&#10094;</button>
                <button class="nav-button nav-next">&#10095;</button>
            </div>
        <?php else: ?>
            <p>Aucun produit disponible dans la galerie.</p>
        <?php endif; ?>
    </main>

    <?php include './includes/footer.php'; ?>

    <!-- Script du slider -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const track = document.querySelector('.slider-track');
            const prevBtn = document.querySelector('.nav-prev');
            const nextBtn = document.querySelector('.nav-next');
            const slides = document.querySelectorAll('.slide');
            let currentIndex = 0;

            const totalSlides = slides.length;

            function updateSlider() {
                const offset = -currentIndex * 100;
                track.style.transform = `translateX(${offset}%)`;
            }

            nextBtn.addEventListener('click', () => {
                currentIndex = (currentIndex + 1) % totalSlides;
                updateSlider();
            });

            prevBtn.addEventListener('click', () => {
                currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
                updateSlider();
            });

            // Auto-slide toutes les 5 secondes
            setInterval(() => {
                currentIndex = (currentIndex + 1) % totalSlides;
                updateSlider();
            }, 5000);
        });
    </script>
</body>
</html>
