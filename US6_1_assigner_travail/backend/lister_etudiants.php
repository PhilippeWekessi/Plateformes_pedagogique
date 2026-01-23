<?php
require_once "../../config/database.php";

header("Content-Type: application/json");

try {
    $stmt = $pdo->query("SELECT id_user as id_etudiant, nom, prenom FROM users WHERE role = 'etudiant'");
    $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($etudiants);
} catch (PDOException $e) {
    echo json_encode([
        "error" => "Erreur de base de données: " . $e->getMessage()
    ]);
}
?>
