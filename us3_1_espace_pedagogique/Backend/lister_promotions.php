<?php
// lister_promotions.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Chemin vers database.php
require_once "../../config/database.php";

try {
    // Récupérer toutes les promotions
    $stmt = $pdo->query("
        SELECT id_promotion, nom_promotion
        FROM promotion
        ORDER BY id_promotion ASC
    ");

    $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "promotions" => $promotions,
        "count" => count($promotions)
    ]);

} catch (PDOException $e) {
    error_log("Erreur PDO lister_promotions: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Erreur de base de données",
        "error" => $e->getMessage(),
        "promotions" => []
    ]);
} catch (Exception $e) {
    error_log("Erreur lister_promotions: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Erreur: " . $e->getMessage(),
        "promotions" => []
    ]);
}
?>
