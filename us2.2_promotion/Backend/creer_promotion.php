<?php
require_once "../../config/database.php";

$nom = trim($_POST['nom'] ?? '');
$annee = trim($_POST['annee_academique'] ?? '');

if ($nom === '' || $annee === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Champs obligatoires manquants"
    ]);
    exit;
}

try {
    // Vérifier si promotion existe
    $check = $pdo->prepare(
        "SELECT id FROM promotion WHERE nom = ? AND annee_academique = ?"
    );
    $check->execute([$nom, $annee]);

    if ($check->rowCount() > 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Promotion existante"
        ]);
        exit;
    }

    // Insertion
    $stmt = $pdo->prepare(
        "INSERT INTO promotion (nom, annee_academique) VALUES (?, ?)"
    );
    $stmt->execute([$nom, $annee]);

    echo json_encode([
        "status" => "success",
        "message" => "Promotion créée avec succès"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Erreur serveur"
    ]);
}
