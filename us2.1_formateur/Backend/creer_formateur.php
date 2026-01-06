<?php
require_once "../../config/database.php";

// Récupérer les données JSON envoyées depuis le frontend
$data = json_decode(file_get_contents("php://input"), true);

if(empty($data['nom']) || empty($data['prenom']) || empty($data['email']) ||
   empty($data['password']) || empty($data['role'])) {
    echo json_encode(["success" => false, "message" => "Tous les champs sont obligatoires"]);
    exit;
}

try {
    // Vérifier si l'email existe déjà
    $stmtCheck = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmtCheck->execute([$data['email']]);
    if($stmtCheck->rowCount() > 0){
        echo json_encode(["success" => false, "message" => "Email déjà utilisé"]);
        exit;
    }

    // Insérer le formateur dans la table utilisateurs
    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs (nom, prenom, email, password, role, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $data['nom'],
        $data['prenom'],
        $data['email'],
        password_hash($data['password'], PASSWORD_DEFAULT),
        $data['role']
    ]);

    echo json_encode(["success" => true, "message" => "Formateur créé avec succès"]);

} catch(Exception $e){
    echo json_encode([
        "success" => false,
        "message" => "Erreur : " . $e->getMessage()
    ]);
}
