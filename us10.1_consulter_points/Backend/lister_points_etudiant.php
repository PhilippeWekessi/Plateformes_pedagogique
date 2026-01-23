<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Vérifier que l'étudiant est connecté
session_start();
if(!isset($_SESSION['etudiant_id'])) {
    echo json_encode([]);
    exit;
}

$etudiant_id = $_SESSION['etudiant_id'];

try {
    $stmt = $pdo->prepare("
        SELECT t.titre, t.type, pt.note, pt.commentaire
        FROM points_travaux pt
        JOIN travaux t ON pt.id_travail = t.id
        WHERE pt.id_etudiant = ?
    ");
    $stmt->execute([$etudiant_id]);
    $points = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($points);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>
