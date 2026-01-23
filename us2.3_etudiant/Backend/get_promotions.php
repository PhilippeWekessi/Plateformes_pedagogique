<?php
// get_promotions.php
// Récupère la liste des promotions depuis la base de données

// Activation du débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers pour CORS et JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Chemin relatif vers le fichier de configuration de la base de données
$config_path = "../../config/database.php";

// Vérifier si le fichier de configuration existe
if (!file_exists($config_path)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fichier de configuration introuvable',
        'path_attempted' => realpath(dirname($config_path))
    ]);
    exit;
}

require_once $config_path;

// Journalisation pour débogage
$log_file = dirname(__FILE__) . '/promotions_debug.log';
$log_message = "[" . date('Y-m-d H:i:s') . "] Début get_promotions.php\n";

try {
    // Vérifier la connexion PDO
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Connexion à la base de données non initialisée');
    }
    
    $log_message .= "Connexion PDO vérifiée\n";
    
    // Requête SQL pour récupérer les promotions
    $sql = "
        SELECT 
            id_promotion, 
            nom_promotion, 
            annee_academique,
            DATE_FORMAT(date_creation, '%d/%m/%Y') as date_creation_format
        FROM promotion 
        WHERE id_promotion IS NOT NULL 
        ORDER BY 
            FIELD(nom_promotion, 'Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2'),
            annee_academique DESC,
            nom_promotion ASC
    ";
    
    $log_message .= "Requête SQL: $sql\n";
    
    // Préparer et exécuter la requête
    $stmt = $pdo->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Erreur de préparation de la requête SQL');
    }
    
    $stmt->execute();
    $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $log_message .= "Nombre de promotions récupérées: " . count($promotions) . "\n";
    
    // Vérifier si des promotions ont été trouvées
    if (empty($promotions)) {
        $response = [
            'success' => true,
            'message' => 'Aucune promotion trouvée',
            'promotions' => [],
            'count' => 0
        ];
    } else {
        // Formatage des données pour la réponse
        $formatted_promotions = array_map(function($promo) {
            return [
                'id_promotion' => (int)$promo['id_promotion'],
                'nom_promotion' => htmlspecialchars($promo['nom_promotion'], ENT_QUOTES, 'UTF-8'),
                'annee_academique' => htmlspecialchars($promo['annee_academique'], ENT_QUOTES, 'UTF-8'),
                'date_creation' => $promo['date_creation_format'],
                'display_text' => htmlspecialchars($promo['nom_promotion'] . ' - ' . $promo['annee_academique'], ENT_QUOTES, 'UTF-8')
            ];
        }, $promotions);
        
        $response = [
            'success' => true,
            'message' => 'Promotions récupérées avec succès',
            'promotions' => $formatted_promotions,
            'count' => count($formatted_promotions),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    // Envoyer la réponse
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
    $log_message .= "Réponse envoyée avec succès\n";
    
} catch (PDOException $e) {
    $error_message = 'Erreur PDO: ' . $e->getMessage();
    $log_message .= "ERREUR PDO: " . $error_message . "\n";
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $error_message,
        'error_code' => $e->getCode()
    ]);
    
} catch (Exception $e) {
    $error_message = 'Erreur: ' . $e->getMessage();
    $log_message .= "ERREUR: " . $error_message . "\n";
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur du serveur',
        'error' => $error_message
    ]);
} finally {
    // Écrire dans le fichier de log
    if (isset($log_file)) {
        file_put_contents($log_file, $log_message . "---\n", FILE_APPEND);
    }
}
?>