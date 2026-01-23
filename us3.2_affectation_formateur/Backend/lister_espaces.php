<?php
require_once "../../config/database.php";

header("Content-Type: application/json");

try {
    $stmt = $pdo->query("
        SELECT id_espace, nom_espace, matiere
        FROM espace_pedagogique
        ORDER BY nom_espace ASC
    ");

    $espaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "espaces" => $espaces,
        "count" => count($espaces)
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur de base de données",
        "error" => $e->getMessage()
    ]);
}
?>
