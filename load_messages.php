<?php
$dataFile = './data/blog.json';

if (!isset($_GET['salon'])) {
    echo json_encode(['success' => false, 'error' => 'Salon invalide.']);
    exit;
}

$salon = $_GET['salon'];

// Charger les données actuelles
$blogData = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

if (!array_key_exists($salon, $blogData)) {
    echo json_encode(['success' => false, 'error' => 'Salon inexistant.']);
    exit;
}

// Récupérer uniquement les messages validés
$messages = array_filter($blogData[$salon], function ($msg) {
    return $msg['validated'];
});

// Retourner les messages en JSON
echo json_encode(['success' => true, 'messages' => $messages]);
