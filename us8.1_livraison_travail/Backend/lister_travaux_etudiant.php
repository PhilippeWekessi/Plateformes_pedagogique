<?php
header('Content-Type: application/json');

// Activer les erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../../config/database.php";

    // ID étudiant fixe pour le développement
    $etudiant_id = $_GET['etudiant_id'] ?? 7;

    // Requête pour obtenir les travaux assignés
    $sql = "
    SELECT t.id_travail, t.titre,
           IFNULL(t.type, 'Non spécifié') as type,
           t.description as consignes,
           t.date_fin
    FROM travail t
    JOIN assigner a ON a.id_travail = t.id_travail
    WHERE a.id_etudiant = ?
    ORDER BY t.date_fin
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$etudiant_id]);
    $travaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "travaux" => $travaux
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur serveur: " . $e->getMessage()
    ]);
}
?>
