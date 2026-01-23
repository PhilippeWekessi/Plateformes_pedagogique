<?php
require_once "../../config/database.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$etudiant_id = $data['etudiant_id'] ?? null;
$espace_id   = $data['espace_id'] ?? null;

if (!$etudiant_id || !$espace_id) {
    echo json_encode([
        "success" => false,
        "message" => "Données manquantes"
    ]);
    exit;
}

// Vérifier si l'étudiant est déjà affecté
$check = $pdo->prepare("
    SELECT id FROM espace_etudiant
    WHERE id_etudiant = ? AND id_espace = ?
");
$check->execute([$etudiant_id, $espace_id]);

if ($check->rowCount() > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Étudiant déjà affecté à cet espace"
    ]);
    exit;
}

// Affectation
$insert = $pdo->prepare("
    INSERT INTO espace_etudiant (id_etudiant, id_espace, statut)
    VALUES (?, ?, 'actif')
");

if ($insert->execute([$etudiant_id, $espace_id])) {
    echo json_encode([
        "success" => true,
        "message" => "Étudiant ajouté avec succès à l’espace pédagogique"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de l’affectation"
    ]);
}
?>
