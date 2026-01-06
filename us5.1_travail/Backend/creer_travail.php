<?php
require_once "../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if(empty($data['titre']) || empty($data['type']) || empty($data['consignes']) || empty($data['date_limite'])){
    echo json_encode(["success"=>false, "message"=>"Tous les champs sont obligatoires"]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO travaux (titre, type_travail, consignes, date_limite, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([
        $data['titre'],
        $data['type'],
        $data['consignes'],
        $data['date_limite']
    ]);

    echo json_encode(["success"=>true, "message"=>"Travail créé avec succès"]);

} catch (Exception $e){
    echo json_encode(["success"=>false, "message"=>"Erreur : ".$e->getMessage()]);
}
?>
