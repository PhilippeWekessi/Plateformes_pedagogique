<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Vérifier que l'utilisateur est connecté et est directeur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'directeur') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

require_once '../../config/database.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['nom']) || !isset($data['prenom']) || !isset($data['email']) || !isset($data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
        exit;
    }
    
    $nom = trim($data['nom']);
    $prenom = trim($data['prenom']);
    $email = trim($data['email']);
    $password = $data['password'];
    
    // Validation du mot de passe
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        echo json_encode(['success' => false, 'message' => 'Format de mot de passe invalide']);
        exit;
    }
    
    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id_user FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Cet email existe déjà']);
        exit;
    }
    
    // Hasher le mot de passe
    $passwordHash = hash('sha256', $password);
    
    // Insérer le formateur
    $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, role, statut, created_at) VALUES (?, ?, ?, ?, 'formateur', 'actif', NOW())");
    $stmt->execute([$nom, $prenom, $email, $passwordHash]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Formateur créé avec succès',
        'formateur_id' => $pdo->lastInsertId()
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
?>