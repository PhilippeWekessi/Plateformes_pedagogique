<?php
header("Content-Type: application/json");
require_once "../../config/database.php";

try {
    $etudiant_id = $_GET['etudiant_id'] ?? null;

    if (!$etudiant_id) {
        echo json_encode([
            "success" => false,
            "message" => "Étudiant non fourni"
        ]);
        exit;
    }

    // Vérification basique de l'existence de l'étudiant
    $stmt = $pdo->prepare("SELECT id_user FROM users WHERE id_user = ? AND role = 'etudiant'");
    $stmt->execute([$etudiant_id]);
    if (!$stmt->fetch()) {
        echo json_encode([
            "success" => false,
            "message" => "Étudiant introuvable"
        ]);
        exit;
    }

    $sql = "
    SELECT
        t.id_travail,
        t.titre,
        t.type,
        t.description AS consignes,
        t.date_fin,
        'En cours' AS statut
    FROM travail t
    JOIN assigner a ON a.id_travail = t.id_travail
    WHERE a.id_etudiant = ?
    ORDER BY t.date_fin
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$etudiant_id]);
    $travaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "travaux" => $travaux
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur serveur: " . $e->getMessage()
    ]);
}
?>
