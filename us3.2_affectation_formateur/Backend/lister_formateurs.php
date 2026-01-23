<?php
require_once "../../config/database.php";

header("Content-Type: application/json");

try {
    $stmt = $pdo->prepare("
        SELECT id_user, nom, prenom
        FROM users
        WHERE role = 'formateur' AND statut = 'actif'
    ");
    $stmt->execute();

    $formateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "formateurs" => $formateurs,
        "count" => count($formateurs)
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur de base de données",
        "error" => $e->getMessage()
    ]);
}
?>
