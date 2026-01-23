<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Récupérer paramètres
$promotion_id = $_GET['promotion'] ?? null;
$annee = $_GET['annee'] ?? null;

if (!$promotion_id || !$annee) {
    echo json_encode([]);
    exit;
}

// Récupérer le classement depuis la base
$stmt = $pdo->prepare("
    SELECT e.nom, e.prenom, SUM(t.note) AS note_totale
    FROM etudiants e
    JOIN travail_etudiant t ON e.id = t.etudiant_id
    WHERE e.promotion_id = :promotion AND t.annee_academique = :annee
    GROUP BY e.id
    ORDER BY note_totale DESC
");
$stmt->execute(['promotion' => $promotion_id, 'annee' => $annee]);
$classement = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($classement);
