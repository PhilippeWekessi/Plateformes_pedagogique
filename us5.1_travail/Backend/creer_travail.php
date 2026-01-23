<?php
session_start();
require_once "../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

// Vérification des champs obligatoires
if(empty($data['titre']) || empty($data['type']) || empty($data['consignes']) || empty($data['date_limite']) || empty($data['id_espace'])){
    echo json_encode(["success"=>false, "message"=>"Tous les champs sont obligatoires"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO travail (titre, type, description, date_fin, id_espace)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['titre'],
        $data['type'],
        $data['consignes'],
        $data['date_limite'],
        $data['id_espace']
    ]);

    echo json_encode(["success"=>true, "message"=>"Travail créé avec succès"]);
} catch (Exception $e){
    echo json_encode(["success"=>false, "message"=>"Erreur : ".$e->getMessage()]);
}
?>
