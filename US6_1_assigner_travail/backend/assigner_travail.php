<?php
session_start();
require_once "../../config/database.php";

header("Content-Type: application/json");

// Lire les données envoyées
$data = json_decode(file_get_contents("php://input"), true);

// Vérification des champs obligatoires
$missingFields = [];
if (empty($data['id_etudiant'])) $missingFields[] = 'étudiant';
if (empty($data['titre'])) $missingFields[] = 'titre';
if (empty($data['type'])) $missingFields[] = 'type';
if (empty($data['consignes'])) $missingFields[] = 'consignes';
if (empty($data['date_limite'])) $missingFields[] = 'date limite';

if (!empty($missingFields)) {
    echo json_encode([
        "success" => false,
        "message" => "Veuillez remplir tous les champs obligatoires: " . implode(', ', $missingFields)
    ]);
    exit;
}

try {
    // D'abord, insérer le travail dans la table travail
    $travailSql = "INSERT INTO travail (titre, type, description, date_fin) VALUES (?, ?, ?, ?)";
    $travailStmt = $pdo->prepare($travailSql);
    $travailStmt->execute([
        $data['titre'],
        $data['type'],
        $data['consignes'],
        $data['date_limite']
    ]);

    // Récupérer l'ID du travail nouvellement créé
    $id_travail = $pdo->lastInsertId();

    // Ensuite, assigner ce travail à l'étudiant dans la table assigner
    $assignationSql = "INSERT INTO assigner (id_travail, id_etudiant, date_assignation) VALUES (?, ?, NOW())";
    $assignationStmt = $pdo->prepare($assignationSql);
    $assignationStmt->execute([
        $id_travail,
        $data['id_etudiant']
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Travail assigné avec succès."
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de l'assignation: " . $e->getMessage()
    ]);
}
?>
