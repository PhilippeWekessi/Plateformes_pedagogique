<?php
require_once "../../config/database.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$etudiant_id = $data['etudiant_id'] ?? null;
$espace_id = $data['espace_id'] ?? null;

if (!$etudiant_id || !$espace_id) {
    echo json_encode([
        "success" => false,
        "message" => "Données manquantes"
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT u.id_user, u.nom, u.prenom, ee.date_affectation, ee.statut
        FROM users u
        JOIN espace_etudiant ee ON u.id_user = ee.id_etudiant
        WHERE ee.id_espace = ? AND ee.id_etudiant = ?
    ");
    $stmt->execute([$espace_id, $etudiant_id]);

    $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($etudiant) {
        echo json_encode([
            "success" => true,
            "exists" => true,
            "etudiant" => $etudiant
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "exists" => false
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur de base de données",
        "error" => $e->getMessage()
    ]);
}
?>
