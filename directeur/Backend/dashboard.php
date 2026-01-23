<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'directeur') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

require_once '../../config/database.php';

try {
    $stats = [];
    
    // Nombre de formateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'formateur' AND statut = 'actif'");
    $stats['formateurs'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Nombre d'étudiants
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'etudiant' AND statut = 'actif'");
    $stats['etudiants'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'user' => [
            'nom' => $_SESSION['user_nom'],
            'prenom' => $_SESSION['user_prenom']
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données']);
}
?>