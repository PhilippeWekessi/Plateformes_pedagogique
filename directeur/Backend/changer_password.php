<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'directeur') {
    echo json_encode(['success' => false, 'message' => 'Session invalide']);
    exit;
}

require_once '../../config/database.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['ancien_password']) || !isset($data['nouveau_password'])) {
        echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
        exit;
    }
    
    $ancienPassword = $data['ancien_password'];
    $nouveauPassword = $data['nouveau_password'];
    
    // Validation du format
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $nouveauPassword)) {
        echo json_encode(['success' => false, 'message' => 'Format de mot de passe invalide']);
        exit;
    }
    
    // Vérifier l'ancien mot de passe - Utiliser id_user
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id_user = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || hash('sha256', $ancienPassword) !== $user['password']) {
        echo json_encode(['success' => false, 'message' => 'Ancien mot de passe incorrect']);
        exit;
    }
    
    // Mettre à jour - Utiliser id_user
    $nouveauPasswordHash = hash('sha256', $nouveauPassword);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id_user = ?");
    $stmt->execute([$nouveauPasswordHash, $_SESSION['user_id']]);
    
    session_destroy();
    
    echo json_encode(['success' => true, 'message' => 'Mot de passe modifié avec succès']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données']);
}
?>