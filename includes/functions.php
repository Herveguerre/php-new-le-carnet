<?php
function getSiteConfig(): mixed {
    $dataFile = __DIR__ . '/../data/data.json'; // Chemin absolu basé sur __DIR__
    if (file_exists(filename: $dataFile)) {
        $data = file_get_contents(filename: $dataFile);
        return json_decode($data, associative: true);
    }
    return [];
}

