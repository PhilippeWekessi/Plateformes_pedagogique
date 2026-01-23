<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once '../../config/database.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['email']) || !isset($data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis']);
        exit;
    }
    
    $email = trim($data['email']);
    $password = $data['password'];
    
    // Vérification avec id_user
    $stmt = $pdo->prepare("SELECT id_user, nom, prenom, email, password, role, statut FROM users WHERE email = ? AND role = 'directeur' AND statut = 'actif'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
        exit;
    }
    
    // IMPORTANT : Si vous avez stocké le mot de passe en clair dans votre INSERT
    // Vous devez comparer directement (ce qui n'est pas sécurisé)
    // Si c'est le cas, pour tester :
    if ($password !== $user['password']) {
        echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect']);
        exit;
    }
    
    // Alternative recommandée si vous avez hashé le mot de passe :
    // if (!password_verify($password, $user['password'])) {
    //     echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect']);
    //     exit;
    // }
    
    // Connexion réussie
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_nom'] = $user['nom'];
    $_SESSION['user_prenom'] = $user['prenom'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => [
            'id' => $user['id_user'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Server error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>