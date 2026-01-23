<?php
require_once "../../config/database.php";

header("Content-Type: application/json");

$promotion_id = $_GET['promotion_id'] ?? null;

if (!$promotion_id) {
    echo json_encode([
        "success" => false,
        "message" => "ID de promotion non fourni"
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id_user, nom, prenom
        FROM users
        WHERE role = 'etudiant' AND promotion_id = ? AND statut = 'actif'
        ORDER BY nom, prenom ASC
    ");

    $stmt->execute([$promotion_id]);

    $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "etudiants" => $etudiants,
        "count" => count($etudiants)
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur de base de données",
        "error" => $e->getMessage()
    ]);
}
?>
