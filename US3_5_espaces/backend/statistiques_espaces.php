<?php
session_start();
require_once "../../config/database.php";

$stats = [];

// Total des espaces pédagogiques
$stats['total_espaces'] = $pdo->query(
    "SELECT COUNT(*) FROM espace_pedagogique"
)->fetchColumn();

// Total des étudiants
$stats['total_etudiants'] = $pdo->query(
    "SELECT COUNT(*) FROM users WHERE role = 'etudiant'"
)->fetchColumn();

// Total des formateurs
$stats['total_formateurs'] = $pdo->query(
    "SELECT COUNT(*) FROM users WHERE role = 'formateur'"
)->fetchColumn();

echo json_encode($stats);
?>
