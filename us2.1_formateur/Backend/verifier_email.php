<?php
require_once "../../config/database.php";

$email = $_GET['email'] ?? '';

if(empty($email)){
    echo json_encode(["exists" => false]);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
$stmt->execute([$email]);
$exists = $stmt->rowCount() > 0;

echo json_encode(["exists" => $exists]);