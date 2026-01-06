<?php
require_once "../config/database.php";

$nom = $_POST['nom'] ?? '';
$matiere = $_POST['matiere'] ?? '';

if (empty($nom) || empty($matiere)) {
    echo json_encode([
        "success" => false,
        "message" => "Tous les champs sont obligatoires"
    ]);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO espaces_pedagogiques (nom, matiere) VALUES (?, ?)");
$stmt->execute([$nom, $matiere]);

echo json_encode([
    "success" => true,
    "message" => "Espace pédagogique créé avec succès"
]);
