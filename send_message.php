<?php
session_start();

$dataFile = './data/blog.json';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user']['username'])) {
    echo json_encode(['success' => false, 'error' => 'Vous devez être connecté pour envoyer un message.']);
    exit;
}

$username = $_SESSION['user']['username'];

// Vérifier les données envoyées
if (!isset($_POST['salon'], $_POST['message'])) {
    echo json_encode(['success' => false, 'error' => 'Données invalides.']);
    exit;
}

$salon = $_POST['salon'];
$message = htmlspecialchars(trim($_POST['message']));

if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Le message ne peut pas être vide.']);
    exit;
}

// Charger les données actuelles
$blogData = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

// Vérifier si le salon existe
if (!array_key_exists($salon, $blogData)) {
    echo json_encode(['success' => false, 'error' => 'Salon invalide.']);
    exit;
}

// Ajouter le message
$blogData[$salon][] = [
    'username' => $username,
    'message' => $message,
    'validated' => false,
    'timestamp' => date('d/m/Y H:i'),
];

// Enregistrer dans le fichier JSON
file_put_contents($dataFile, json_encode($blogData, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Votre message a été envoyé et sera visible après validation.']);
