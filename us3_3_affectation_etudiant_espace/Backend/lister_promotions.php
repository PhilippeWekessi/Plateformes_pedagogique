<?php
require_once "../../config/database.php";

header("Content-Type: application/json");

try {
    $stmt = $pdo->query("
        SELECT id_promotion, nom_promotion, annee_academique
        FROM promotion
        ORDER BY nom_promotion ASC
    ");

    $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "promotions" => $promotions,
        "count" => count($promotions)
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur de base de données",
        "error" => $e->getMessage()
    ]);
}
?>
