<?php
session_start();
require_once "../../config/database.php";

$id_travail = $_GET['id_travail'] ?? null;
$id_etudiant = $_SESSION['id_user'] ?? null; // Utiliser l'ID de la session

if (!$id_travail || !$id_etudiant) {
    echo json_encode(['livre' => false]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM livraison WHERE id_travail = ? AND id_etudiant = ?");
$stmt->execute([$id_travail, $id_etudiant]);
echo json_encode(['livre' => $stmt->rowCount() > 0]);
?>
