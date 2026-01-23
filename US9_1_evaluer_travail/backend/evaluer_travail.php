<?php
session_start();
header('Content-Type: application/json');
require_once "../../config/database.php";

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    echo json_encode([
        "success" => false,
        "message" => "Accès non autorisé: utilisateur non connecté"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['livraison']) ||
    empty($data['note']) ||
    empty($data['commentaire'])
) {
    echo json_encode([
        "success" => false,
        "message" => "Veuillez renseigner tous les champs."
    ]);
    exit;
}

// Vérifier que la note est valide
if (!is_numeric($data['note']) || $data['note'] < 0 || $data['note'] > 20) {
    echo json_encode([
        "success" => false,
        "message" => "La note doit être un nombre entre 0 et 20."
    ]);
    exit;
}

try {
    // Vérifier que la livraison existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM livraison WHERE id_livraison = ?");
    $stmt->execute([$data['livraison']]);

    if ($stmt->fetchColumn() === 0) {
        echo json_encode([
            "success" => false,
            "message" => "La livraison spécifiée n'existe pas."
        ]);
        exit;
    }

    // Vérifier que la livraison n'a pas déjà été évaluée
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM evaluation WHERE id_livraison = ?");
    $stmt->execute([$data['livraison']]);

    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Ce travail a déjà été évalué."
        ]);
        exit;
    }

    // Insérer l'évaluation
    $sql = "INSERT INTO evaluation
            (id_livraison, note, commentaire, id_formateur, date_evaluation)
            VALUES (?, ?, ?, ?, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['livraison'],
        $data['note'],
        $data['commentaire'],
        $_SESSION['id_user']
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Évaluation enregistrée avec succès."
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur base de données: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur serveur: " . $e->getMessage()
    ]);
}
?>
