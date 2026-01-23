<?php
// creer_etudiant.php
// Gère la création d'un nouvel étudiant

// Activation du débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers pour CORS et JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Vérifier que la méthode est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée. Utilisez POST.'
    ]);
    exit;
}

// Journalisation pour débogage
$log_file = dirname(__FILE__) . '/etudiants_debug.log';
$log_message = "[" . date('Y-m-d H:i:s') . "] Début creer_etudiant.php\n";

// Récupérer les données de la requête
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
$raw_input = file_get_contents('php://input');

$log_message .= "Content-Type: $content_type\n";
$log_message .= "Raw input: " . substr($raw_input, 0, 500) . "\n";

// Déterminer comment parser les données
if (strpos($content_type, 'application/json') !== false) {
    $data = json_decode($raw_input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Données JSON invalides',
            'json_error' => json_last_error_msg()
        ]);
        exit;
    }
} else if (strpos($content_type, 'application/x-www-form-urlencoded') !== false) {
    parse_str($raw_input, $data);
} else {
    // Par défaut, utiliser $_POST
    $data = $_POST;
}

$log_message .= "Données parsées: " . print_r($data, true) . "\n";

// Chemin vers le fichier de configuration
$config_path = "../../config/database.php";

// Vérifier si le fichier de configuration existe
if (!file_exists($config_path)) {
    $log_message .= "ERREUR: Fichier de configuration introuvable: $config_path\n";
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Configuration de la base de données introuvable',
        'config_path' => realpath(dirname($config_path))
    ]);
    
    file_put_contents($log_file, $log_message . "---\n", FILE_APPEND);
    exit;
}

require_once $config_path;

try {
    // Vérifier la connexion PDO
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Connexion à la base de données non initialisée');
    }
    
    $log_message .= "Connexion PDO vérifiée\n";
    
    // Valider et nettoyer les données
    $errors = [];
    
    // Nom
    $nom = isset($data['nom']) ? trim($data['nom']) : '';
    if (empty($nom)) {
        $errors['nom'] = 'Le nom est obligatoire';
    } else if (strlen($nom) < 2) {
        $errors['nom'] = 'Le nom doit contenir au moins 2 caractères';
    } else if (strlen($nom) > 100) {
        $errors['nom'] = 'Le nom ne peut pas dépasser 100 caractères';
    } else {
        $nom = htmlspecialchars($nom, ENT_QUOTES, 'UTF-8');
    }
    
    // Prénom
    $prenom = isset($data['prenom']) ? trim($data['prenom']) : '';
    if (empty($prenom)) {
        $errors['prenom'] = 'Le prénom est obligatoire';
    } else if (strlen($prenom) < 2) {
        $errors['prenom'] = 'Le prénom doit contenir au moins 2 caractères';
    } else if (strlen($prenom) > 100) {
        $errors['prenom'] = 'Le prénom ne peut pas dépasser 100 caractères';
    } else {
        $prenom = htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8');
    }
    
    // Email
    $email = isset($data['email']) ? trim($data['email']) : '';
    if (empty($email)) {
        $errors['email'] = 'L\'email est obligatoire';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format d\'email invalide';
    } else if (strlen($email) > 255) {
        $errors['email'] = 'L\'email ne peut pas dépasser 255 caractères';
    } else {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    
    // Promotion ID
    $promotion_id = isset($data['promotion_id']) ? (int)$data['promotion_id'] : 0;
    if ($promotion_id <= 0) {
        $errors['promotion_id'] = 'Une promotion valide doit être sélectionnée';
    }
    
    // Si erreurs de validation, retourner immédiatement
    if (!empty($errors)) {
        $log_message .= "Erreurs de validation: " . print_r($errors, true) . "\n";
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Erreurs de validation',
            'errors' => $errors,
            'fields' => array_keys($errors)
        ]);
        
        file_put_contents($log_file, $log_message . "---\n", FILE_APPEND);
        exit;
    }
    
    $log_message .= "Validation réussie pour: $prenom $nom ($email), promotion: $promotion_id\n";
    
    // Vérifier si l'email existe déjà
    $check_email_sql = "SELECT id_user FROM users WHERE email = ?";
    $check_email_stmt = $pdo->prepare($check_email_sql);
    $check_email_stmt->execute([$email]);
    
    if ($check_email_stmt->rowCount() > 0) {
        $log_message .= "ERREUR: Email déjà utilisé: $email\n";
        
        http_response_code(409); // Conflict
        echo json_encode([
            'success' => false,
            'message' => 'Un utilisateur avec cet email existe déjà',
            'field' => 'email',
            'error' => 'email_exists'
        ]);
        
        file_put_contents($log_file, $log_message . "---\n", FILE_APPEND);
        exit;
    }
    
    $log_message .= "Email $email disponible\n";
    
    // Vérifier si la promotion existe
    $check_promo_sql = "SELECT id_promotion, nom_promotion, annee_academique FROM promotion WHERE id_promotion = ?";
    $check_promo_stmt = $pdo->prepare($check_promo_sql);
    $check_promo_stmt->execute([$promotion_id]);
    $promotion = $check_promo_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$promotion) {
        $log_message .= "ERREUR: Promotion introuvable ID: $promotion_id\n";
        
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Promotion introuvable',
            'field' => 'promotion_id',
            'error' => 'promotion_not_found'
        ]);
        
        file_put_contents($log_file, $log_message . "---\n", FILE_APPEND);
        exit;
    }
    
    $log_message .= "Promotion trouvée: " . $promotion['nom_promotion'] . "\n";
    
    // Générer un mot de passe temporaire sécurisé
    function genererMotDePasse($longueur = 12) {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*';
        $mot_de_passe = '';
        $max = strlen($caracteres) - 1;
        
        for ($i = 0; $i < $longueur; $i++) {
            $mot_de_passe .= $caracteres[random_int(0, $max)];
        }
        
        return $mot_de_passe;
    }
    
    $mot_de_passe_temporaire = genererMotDePasse();
    $mot_de_passe_hash = password_hash($mot_de_passe_temporaire, PASSWORD_DEFAULT);
    
    $log_message .= "Mot de passe temporaire généré: $mot_de_passe_temporaire (hashé)\n";
    
    // Démarrer une transaction pour assurer l'intégrité des données
    $pdo->beginTransaction();
    
    try {
        // Insertion dans la table users
        $insert_sql = "
            INSERT INTO users (
                nom, 
                prenom, 
                email, 
                password, 
                role, 
                promotion_id, 
                statut, 
                created_at
            ) VALUES (
                :nom, 
                :prenom, 
                :email, 
                :password, 
                'etudiant', 
                :promotion_id, 
                'actif', 
                NOW()
            )
        ";
        
        $log_message .= "SQL d'insertion: $insert_sql\n";
        
        $insert_stmt = $pdo->prepare($insert_sql);
        
        $insert_data = [
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':password' => $mot_de_passe_hash,
            ':promotion_id' => $promotion_id
        ];
        
        $log_message .= "Données d'insertion: " . print_r($insert_data, true) . "\n";
        
        $result = $insert_stmt->execute($insert_data);
        
        if (!$result) {
            throw new Exception('Échec de l\'insertion dans la base de données');
        }
        
        $etudiant_id = $pdo->lastInsertId();
        
        $log_message .= "Insertion réussie. ID étudiant: $etudiant_id\n";
        
        // Valider la transaction
        $pdo->commit();
        
        // Préparer la réponse de succès
        $response = [
            'success' => true,
            'message' => '✅ Étudiant créé avec succès !',
            'data' => [
                'id_etudiant' => (int)$etudiant_id,
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'promotion' => [
                    'id' => (int)$promotion_id,
                    'nom' => $promotion['nom_promotion'],
                    'annee_academique' => $promotion['annee_academique']
                ],
                'password_temp' => $mot_de_passe_temporaire,
                'created_at' => date('Y-m-d H:i:s'),
                'instructions' => [
                    'message' => 'Ces identifiants doivent être communiqués à l\'étudiant',
                    'email_suggestion' => 'Envoyez un email avec ces informations à ' . $email
                ]
            ],
            'metadata' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'transaction_id' => uniqid('etudiant_', true)
            ]
        ];
        
        $log_message .= "Transaction validée. Réponse préparée.\n";
        
        // Envoyer la réponse
        http_response_code(201); // Created
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        // Optionnel : Envoyer un email de bienvenue
        // $this->envoyerEmailBienvenue($email, $prenom, $nom, $mot_de_passe_temporaire, $promotion);
        
    } catch (Exception $e) {
        // Rollback en cas d'erreur
        $pdo->rollBack();
        
        $log_message .= "ERREUR transaction: " . $e->getMessage() . "\n";
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la création de l\'étudiant',
            'error' => $e->getMessage(),
            'error_type' => 'transaction_error'
        ]);
    }
    
} catch (PDOException $e) {
    $error_message = 'Erreur PDO: ' . $e->getMessage();
    $log_message .= "ERREUR PDO: " . $error_message . "\n";
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $error_message,
        'error_code' => $e->getCode(),
        'error_type' => 'pdo_exception'
    ]);
    
} catch (Exception $e) {
    $error_message = 'Erreur: ' . $e->getMessage();
    $log_message .= "ERREUR: " . $error_message . "\n";
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur du serveur',
        'error' => $error_message,
        'error_type' => 'general_exception'
    ]);
} finally {
    // Écrire dans le fichier de log
    if (isset($log_file)) {
        file_put_contents($log_file, $log_message . "---\n", FILE_APPEND);
    }
}
?>