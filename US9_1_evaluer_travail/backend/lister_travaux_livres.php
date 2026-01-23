<?php
header('Content-Type: application/json');
require_once "../../config/database.php";

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $sql = "
    SELECT l.id_livraison, t.titre,
           CONCAT(u.nom, ' ', u.prenom) AS etudiant,
           l.date_livraison
    FROM livraison l
    JOIN travail t ON l.id_travail = t.id_travail
    JOIN users u ON l.id_etudiant = u.id_user
    LEFT JOIN evaluation ev ON l.id_livraison = ev.id_livraison
    WHERE ev.id_livraison IS NULL
    ";

    $stmt = $pdo->query($sql);
    $travaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($travaux);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur serveur: " . $e->getMessage()
    ]);
}
?>
