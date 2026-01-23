<?php
require_once "../../config/database.php";

$id_formateur = $_GET['id_formateur'];
$id_espace = $_GET['id_espace'];

$stmt = $pdo->prepare("
    SELECT id FROM espace_formateur
    WHERE id_formateur = ? AND id_espace = ?
");
$stmt->execute([$id_formateur, $id_espace]);

echo json_encode([
    "exists" => $stmt->rowCount() > 0
]);
?>
