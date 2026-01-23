<?php
require_once "../../config/database.php";

header("Content-Type: application/json");

$formateur_id = $_POST['formateur_id'] ?? null;
$espace_id = $_POST['espace_id'] ?? null;

if (!$formateur_id || !$espace_id) {
    echo json_encode([
        "success" => false,
        "message" => "Informations manquantes: formateur_id ou espace_id non fourni."
    ]);
    exit;
}

// Vérifier si déjà affecté
$check = $pdo->prepare("
    SELECT id FROM espace_formateur
    WHERE id_formateur = ? AND id_espace = ?
");
$check->execute([$formateur_id, $espace_id]);

if ($check->rowCount() > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Formateur déjà affecté à cet espace pédagogique."
    ]);
    exit;
}

// Insertion
try {
    $stmt = $pdo->prepare("
        INSERT INTO espace_formateur (id_formateur, id_espace)
        VALUES (?, ?)
    ");

    $stmt->execute([$formateur_id, $espace_id]);

    echo json_encode([
        "success" => true,
        "message" => "Formateur affecté avec succès à l'espace pédagogique."
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de l'insertion dans la base de données: " . $e->getMessage()
    ]);
}
?>
