<?php
// lister_espaces.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Chemin vers database.php
require_once "../../config/database.php";

try {
    // Récupérer tous les espaces pédagogiques
    $stmt = $pdo->query("
        SELECT
            id_espace,
            nom_espace as nom,
            matiere,
            annee_academique,
            id_promotion
        FROM espace_pedagogique
        ORDER BY id_espace DESC
    ");

    $espaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "espaces" => $espaces,
        "count" => count($espaces)
    ]);

} catch (PDOException $e) {
    error_log("Erreur PDO lister_espaces: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Erreur de base de données",
        "error" => $e->getMessage(),
        "espaces" => []
    ]);
} catch (Exception $e) {
    error_log("Erreur lister_espaces: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Erreur: " . $e->getMessage(),
        "espaces" => []
    ]);
}
?>
