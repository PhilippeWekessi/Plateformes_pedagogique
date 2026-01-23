<?php
// RENOMMEZ CE FICHIER EN database.php ET CONFIGUREZ VOS PARAMÈTRES

$host = "localhost";
$dbname = "plateforme_pedagogique_db_v2";
$user = "root";
$pass = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur connexion base de données"
    ]);
    exit;
}
?>