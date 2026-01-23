<?php
// creer_espace.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// DEBUG: Afficher la méthode pour vérifier
error_log("Méthode HTTP: " . $_SERVER['REQUEST_METHOD']);

// Vérifier la méthode - Accepter GET pour le test mais normalement seulement POST
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Mode test - retourner des infos sur l'API
    echo json_encode([
        "success" => true,
        "message" => "API Espace Pédagogique - En attente de requête POST",
        "endpoint" => "creer_espace.php",
        "method_required" => "POST",
        "parameters_required" => ["nom", "matiere"],
        "parameters_optional" => ["id_promotion"],
        "timestamp" => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Pour les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Méthode " . $_SERVER['REQUEST_METHOD'] . " non autorisée. Utilisez POST.",
        "received_method" => $_SERVER['REQUEST_METHOD'],
        "expected_method" => "POST"
    ]);
    exit;
}

// Chemin vers database.php
$config_path = "../../config/database.php";

// DEBUG: Vérifier le chemin
error_log("Chemin config: " . realpath($config_path));

if (!file_exists($config_path)) {
    echo json_encode([
        "success" => false,
        "message" => "Fichier de configuration introuvable",
        "config_path" => $config_path,
        "real_path" => realpath(dirname($config_path))
    ]);
    exit;
}

require_once $config_path;

try {
    // Vérifier la connexion PDO
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Connexion à la base de données non initialisée');
    }

    // Récupérer les données POST
    $input = file_get_contents('php://input');
    error_log("Données brutes reçues: " . $input);

    // Déterminer comment parser les données
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($content_type, 'application/json') !== false) {
        $data = json_decode($input, true);
    } else if (strpos($content_type, 'application/x-www-form-urlencoded') !== false) {
        parse_str($input, $data);
    } else {
        // Par défaut, utiliser $_POST
        $data = $_POST;
    }

    error_log("Données parsées: " . print_r($data, true));

    // Récupérer les données
    $nom_espace = trim($data['nom'] ?? '');
    $matiere = trim($data['matiere'] ?? '');
    $id_promotion = isset($data['id_promotion']) ? (int)trim($data['id_promotion']) : NULL;

    // Validation
    if (empty($nom_espace) || empty($matiere)) {
        echo json_encode([
            "success" => false,
            "message" => "Tous les champs obligatoires ne sont pas remplis",
            "received_data" => $data
        ]);
        exit;
    }

    // Année académique (année courante)
    $annee_courante = date('Y');
    $annee_academique = $annee_courante . '-' . ($annee_courante + 1);

    // Vérifier si l'espace existe déjà
    $check = $pdo->prepare("SELECT id_espace FROM espace_pedagogique WHERE nom_espace = ?");
    $check->execute([$nom_espace]);

    if ($check->rowCount() > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Un espace avec ce nom existe déjà"
        ]);
        exit;
    }

    // Insertion
    $sql = "INSERT INTO espace_pedagogique (nom_espace, annee_academique, matiere, id_promotion) VALUES (?, ?, ?, ?)";
    error_log("SQL: " . $sql);
    error_log("Valeurs: $nom_espace, $annee_academique, $matiere, $id_promotion");

    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$nom_espace, $annee_academique, $matiere, $id_promotion]);

    if ($success) {
        $id_espace = $pdo->lastInsertId();

        echo json_encode([
            "success" => true,
            "message" => "✅ Espace pédagogique '$nom_espace' créé avec succès!",
            "id_espace" => $id_espace,
            "data" => [
                'nom_espace' => $nom_espace,
                'matiere' => $matiere,
                'annee_academique' => $annee_academique,
                'id_promotion' => $id_promotion,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Erreur lors de l'insertion dans la base de données"
        ]);
    }

} catch (PDOException $e) {
    error_log("Erreur PDO: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Erreur de base de données",
        "error" => $e->getMessage(),
        "error_code" => $e->getCode()
    ]);
} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Erreur: " . $e->getMessage()
    ]);
}
?>
