<?php
session_start();
require_once "../../config/database.php";

// Désactiver temporairement la vérification du rôle pour le développement
/*
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'directeur') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}
*/

$sql = "
SELECT
    ep.id_espace,
    ep.nom_espace,
    p.nom_promotion AS promotion,
    (SELECT CONCAT(u.nom, ' ', u.prenom)
     FROM espace_formateur ef
     JOIN users u ON ef.id_formateur = u.id_user
     WHERE ef.id_espace = ep.id_espace
     LIMIT 1) AS formateur,
    (SELECT COUNT(*)
     FROM espace_etudiant ee
     WHERE ee.id_espace = ep.id_espace) AS nombre_etudiants
FROM espace_pedagogique ep
LEFT JOIN promotion p ON ep.id_promotion = p.id_promotion
ORDER BY ep.nom_espace
";

try {
    $stmt = $pdo->query($sql);
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resultats);
} catch (PDOException $e) {
    echo json_encode([
        "error" => "Erreur de base de données: " . $e->getMessage()
    ]);
}
?>
