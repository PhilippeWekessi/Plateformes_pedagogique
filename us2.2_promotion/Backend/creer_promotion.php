<?php
// Activer le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définir le header JSON
header('Content-Type: application/json');

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Méthode non autorisée"
    ]);
    exit;
}

require_once "../../config/database.php";

// Vérifier si les données sont bien reçues
if (!isset($_POST['nom']) || !isset($_POST['annee_academique'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Données manquantes. Reçu: " . json_encode($_POST)
    ]);
    exit;
}

$nom_promotion = trim($_POST['nom']);
$annee_academique = trim($_POST['annee_academique']);

if ($nom_promotion === '' || $annee_academique === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Tous les champs sont obligatoires"
    ]);
    exit;
}

try {
    // Vérifier la connexion PDO
    if (!$pdo) {
        throw new Exception("Connexion à la base de données échouée");
    }
    
    // CORRECTION ICI : Utiliser les bons noms de colonnes
    // Vérifier si la promotion existe déjà
    $check = $pdo->prepare("SELECT id_promotion FROM promotion WHERE nom_promotion = ? AND annee_academique = ?");
    $check->execute([$nom_promotion, $annee_academique]);
    
    if ($check->rowCount() > 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Cette promotion existe déjà pour cette année académique"
        ]);
        exit;
    }
    
    // CORRECTION ICI : Insérer avec les bons noms de colonnes
    // La date_creation sera automatiquement définie si c'est un TIMESTAMP
    // Si c'est un champ DATE, utilisez CURRENT_DATE
    $stmt = $pdo->prepare("INSERT INTO promotion (nom_promotion, annee_academique, date_creation) VALUES (?, ?, CURDATE())");
    $success = $stmt->execute([$nom_promotion, $annee_academique]);
    
    if ($success) {
        // Récupérer l'ID de la nouvelle promotion
        $lastId = $pdo->lastInsertId();
        
        echo json_encode([
            "status" => "success",
            "message" => "Promotion '{$nom_promotion}' créée avec succès pour l'année {$annee_academique}",
            "id" => $lastId
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Échec de l'insertion dans la base de données"
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Erreur PDO: " . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "Erreur base de données: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "Erreur: " . $e->getMessage()
    ]);
}
?>